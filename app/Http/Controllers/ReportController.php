<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
use App\Models\IdentificationType;
use App\Models\Legacy\Extravio;
use App\Models\Legacy\Identificacion;
use App\Models\LostStatus;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $currentYear = date('Y');
        $currentMonth = date('n');
        $years = range($currentYear, 2022);
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        if ($request->query('year') == $currentYear) {
            $months = array_slice($months, 0, $currentMonth, true);
        }

        $lost_statuses = LostStatus::all();

        return Inertia::render('Reports/Index', [
            'years' => $years,
            'months' => $months,
            'lost_statuses' => $lost_statuses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getByYear(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
        ]);

        $status_name = null;
        if ($request->status) {
            $lost_status = LostStatus::find($request->status);
            $status_name = $lost_status->name ?? null;
        }

        $month = 12;

        for ($i=1; $i <= $month; $i++) { 
            $extraviosINE = Extravio::select('ID_EXTRAVIO')
                ->where('ID_IDENTIFICACION', 1)
                // ->when($request->status, fn($query) => $query->where('ID_ESTADO_EXTRAVIO', $request->status))
                ->where('ID_ESTADO_EXTRAVIO', $request->status)
                ->whereYear('FECHA_REGISTRO', $request->year)
                ->whereMonth('FECHA_REGISTRO', $i)
                ->count();

            $extraviosVisa = Extravio::select('ID_EXTRAVIO')
                ->where('ID_IDENTIFICACION', 2)
                // ->when($request->status, fn($query) => $query->where('ID_ESTADO_EXTRAVIO', $request->status))
                ->where('ID_ESTADO_EXTRAVIO', $request->status)
                ->whereYear('FECHA_REGISTRO', $request->year)
                ->whereMonth('FECHA_REGISTRO', $i)
                ->count();
        }

        // Obtener datos del sistema legacy

        // dd($extravios);

        $misplacements = Misplacement::with('misplacementIdentifications.identificationType')
            ->when($request->status, fn($query) => $query->where('lost_status_id', $request->status))
            ->whereYear('registration_date', $request->year)
            ->get();

        $identifications = IdentificationType::all();

        // Agrupar datos por mes
        $groupedLegacy = $extravios->groupBy(fn($item) => Carbon::parse($item->FECHA_REGISTRO)->format('F'));
        $groupedNew = $misplacements->groupBy(fn($item) => Carbon::parse($item->registration_date)->format('F'));

        $currentYear = Carbon::now()->year;
        $maxMonths = ($request->year == $currentYear) ? Carbon::now()->month : 12;
        $allMonths = collect(range(1, $maxMonths))->mapWithKeys(fn($m) => [
            Carbon::create()->month($m)->format('F') => [
                'total_solicitudes' => 0,
                'identifications_count' => $identifications->pluck('name')->mapWithKeys(fn($name) => [$name => 0])->toArray()
            ]
        ]);

        // Clonar la colección a un array mutable
        $report = $allMonths->toArray();

        foreach ($groupedNew as $month => $items) {
            $monthData = $report[$month] ?? ['total_solicitudes' => 0, 'identifications_count' => []];
            $monthData['total_solicitudes'] += $items->count();
            foreach ($items as $misplacement) {
                if ($misplacement->misplacementIdentifications) {
                    $identificationType = $misplacement->misplacementIdentifications->identificationType;
                    $name = $identificationType ? $identificationType->name : 'Desconocido';
                    $monthData['identifications_count'][$name] = ($monthData['identifications_count'][$name] ?? 0) + 1;
                }
            }
            $report[$month] = $monthData;
        }

        foreach ($groupedLegacy as $month => $items) {
            $monthData = $report[$month] ?? ['total_solicitudes' => 0, 'identifications_count' => []];
            $monthData['total_solicitudes'] += $items->count();
            foreach ($items as $extravio) {
                if ($extravio->identificacion) {
                    $name = $extravio->identificacion->IDENTIFICACION ?? 'Desconocido';
                    $monthData['identifications_count'][$name] = ($monthData['identifications_count'][$name] ?? 0) + 1;
                }
            }
            $report[$month] = $monthData;
        }

        $data = [
            'year' => $request->year,
            'data' => $report,
            'status_name' => $status_name,
        ];

        Log::info('Emails exported by user: ' . Auth::id());
        return (new ExcelRequest())->create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
