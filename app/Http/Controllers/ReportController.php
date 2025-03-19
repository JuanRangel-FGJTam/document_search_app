<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
use App\Models\IdentificationType;
use App\Models\Legacy\Extravio;
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

        $status_name =  null;
        $lost_status = LostStatus::find($request->status);

        if ($request->status) {
            $status_name = $lost_status->name;
        }
        dd(Extravio::all());

        $identifications = IdentificationType::all();

        $misplacements = Misplacement::with('misplacementIdentifications.identificationType')
            ->when(!is_null($request->status), function ($query) use ($request) {
                return $query->where('lost_status_id', $request->status);
            })
            ->whereYear('registration_date', $request->year)
            ->get();

        // Agrupar las solicitudes por mes
        $misplacementsGroupedByMonth = $misplacements->groupBy(function ($misplacement) {
            return Carbon\Carbon::parse($misplacement->registration_date)->format('F'); // Agrupar por nombre del mes
        });

        $currentYear = Carbon\Carbon::now()->year;
        $currentMonth = Carbon\Carbon::now()->month;

        // Determinar el número de meses a incluir
        $maxMonths = ($request->year == $currentYear) ? $currentMonth : 12;
        // Crear un array con los meses hasta el actual si es el año en curso
        $allMonths = [];
        for ($month = 1; $month <= $maxMonths; $month++) {
            $monthName = Carbon\Carbon::create()->month($month)->format('F');
            $allMonths[$monthName] = [
                'total_solicitudes' => 0,
                'identifications_count' => $identifications->pluck('name')->mapWithKeys(function ($name) {
                    return [$name => 0]; // Inicializar todas las identificaciones con 0
                })->toArray()
            ];
        }

        // Contar los tipos de identificación por mes con nombres
        $report = $misplacementsGroupedByMonth->map(function ($items) use ($identifications) {
            // Obtener todos los tipos de identificación relacionados
            $identificationsCount = $identifications->pluck('name')->mapWithKeys(function ($name) {
                return [$name => 0]; // Inicializar todas las identificaciones con 0
            })->toArray();

            $items->each(function ($misplacement) use (&$identificationsCount) {
                if ($misplacement->misplacementIdentifications) {
                    $identificationName = $misplacement->misplacementIdentifications->identificationType->name ?? 'Desconocido';
                    $identificationsCount[$identificationName] = ($identificationsCount[$identificationName] ?? 0) + 1;
                }
            });

            return [
                'total_solicitudes' => $items->count(),
                'identifications_count' => $identificationsCount
            ];
        });

        // Combinar los meses con registros y los meses sin registros
        $report = array_merge($allMonths, $report->toArray());
        $data = [
            'year' => $request->year,
            'data' => $report,
            'status_name' => $status_name,
        ];

        $excel = new ExcelRequest();
        Log::info('Emails exported by user: ' . Auth::id());
        return $excel->create($data);
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
