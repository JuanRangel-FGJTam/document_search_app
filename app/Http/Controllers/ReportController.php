<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
use App\Models\DocumentType;
use App\Models\IdentificationType;
use App\Models\Legacy\Extravio;
use App\Models\Legacy\Identificacion;
use App\Models\Legacy\Objeto;
use App\Models\Legacy\TipoDocumento;
use App\Models\LostStatus;
use App\Models\ReportType;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $report_types = ReportType::all();

        return Inertia::render('Reports/Index',[
            'report_types'=>$report_types
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createByYear(Request $request)
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

        return Inertia::render('Reports/CreateByYear', [
            'years' => $years,
            'months' => $months,
            'lost_statuses' => $lost_statuses
        ]);
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

        // Obtener tipos de identificación
        $identifications = DocumentType::pluck('name', 'id')->map(function ($item) {
            return strtolower($item);
        });
        $identifications_legacy = TipoDocumento::pluck('DOCUMENTO', 'ID_TIPO_DOCUMENTO')->mapWithKeys(function ($item, $key) {
            return [
                $key => match (strtolower($item)) {
                    'credencial de elector' => 'credencial de elector',
                    'pasaporte' => 'pasaporte',
                    'visa' => 'visa',
                    'licencia de conducir' => 'licencia de conducir',
                    'otro documento' => 'otro documento',
                    default => 'otro',
                }
            ];
        })->map(function ($item) {
            return strtolower($item);
        });

        // Inicializar estructura de la tabla
        $currentYear = Carbon::now()->year;
        $maxMonths = ($request->year == $currentYear) ? Carbon::now()->month : 12;
        $report = collect(range(1, $maxMonths))->mapWithKeys(fn($m) => [
            Carbon::create()->month($m)->format('F') => [
                'total_solicitudes' => 0,
                'identifications_count' => $identifications->mapWithKeys(fn($name) => [$name => 0])->toArray(),
            ]
        ])->toArray();

        // Se obtiene mes, id de identificacion y el total
        $extravios = Extravio::selectRaw('MONTH(PGJ_EXTRAVIOS.FECHA_REGISTRO) as mes, PGJ_OBJETOS.ID_TIPO_DOCUMENTO, COUNT(*) as total')
            ->join('PGJ_OBJETOS', 'PGJ_OBJETOS.ID_EXTRAVIO', '=', 'PGJ_EXTRAVIOS.ID_EXTRAVIO')
            ->whereYear('PGJ_EXTRAVIOS.FECHA_REGISTRO', $request->year)
            ->when($request->status, fn($query) => $query->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', $request->status))
            ->groupByRaw('MONTH(PGJ_EXTRAVIOS.FECHA_REGISTRO), PGJ_OBJETOS.ID_TIPO_DOCUMENTO')
            ->get();

        // Se obtiene mes, id de tipo de documento y el total
        $misplacements = Misplacement::selectRaw('MONTH(misplacements.registration_date) as mes, ld.document_type_id, COUNT(*) as total')
            ->join('lost_documents as ld', 'misplacements.id', '=', 'ld.misplacement_id')
            ->whereYear('misplacements.registration_date', $request->year)
            ->when($request->status, fn($query) => $query->where('misplacements.lost_status_id', $request->status))
            ->groupByRaw('MONTH(misplacements.registration_date), ld.document_type_id')
            ->get();


        // Se coloca el nombre del id guardado anteriormente
        foreach ($extravios as $item) {
            $mes = Carbon::create()->month((int) $item->mes)->format('F');
            $identification_name = $identifications_legacy[$item->ID_TIPO_DOCUMENTO] ?? 'otro';
            //dd($identification_name);

            if ($identification_name === 'otro') {
                $identification_name = 'otro documento';
            }
            $report[$mes]['identifications_count'][$identification_name] += $item->total;
            $report[$mes]['total_solicitudes'] += $item->total;
        }

        // Se coloca el nombre del id guardado anteriormente
        foreach ($misplacements as $item) {
            $mes = Carbon::create()->month((int) $item->mes)->format('F');
            $identification_name = $identifications[$item->document_type_id] ?? 'otro';
            if ($identification_name === 'otro') {
                $identification_name = 'otro documento';
            }
            $report[$mes]['identifications_count'][$identification_name] += $item->total;
            $report[$mes]['total_solicitudes'] += $item->total;
        }

        $data = [
            'year' => $request->year,
            'data' => $report,
            'status_name' => $status_name,
        ];

        Log::info('Reporte exportado por usuario: ' . Auth::id());
        return (new ExcelRequest())->create($data);
    }

    public function createSurveys(Request $request){
        return Inertia::render('Reports/CreateSurveys');
    }


    public function getSurveys(Request $request){

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
