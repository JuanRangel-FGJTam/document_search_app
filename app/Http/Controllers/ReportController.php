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
use App\Models\Survey;
use App\Services\AuthApiService;

class ReportController extends Controller
{

    protected $authApiService;

    public function __construct(AuthApiService $authApiService)
    {
        $this->authApiService = $authApiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $report_types = ReportType::all();

        return Inertia::render('Reports/Index', [
            'report_types' => $report_types
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
        $municipalities = null;
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

        if ($request->municipality) {
            $municipalities = $this->authApiService->getMunicipalities();
        }


        $lost_statuses = LostStatus::whereNotIn('id', [1, 2])->get();
        $report_types = ReportType::whereNotIn('id', [5])->get();
        return Inertia::render('Reports/CreateByYear', [
            'years' => $years,
            'months' => $months,
            'lost_statuses' => $lost_statuses,
            'report_types' => $report_types,
            'municipalities' => $municipalities
        ]);
    }

    public function generateReport(Request $request)
    {

        $request->validate([
            'reportType' => 'required|integer',
            'year' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'municipality' => 'nullable|numeric',
            'status' => 'nullable|numeric',
        ]);

        $filters = null;
        switch ($request->reportType) {
            case 1:
                $filters = [
                    'year' => $request->year,
                    'status' => $request->status ?? null,
                    'municipio' => null, // No se filtra por municipio en este caso
                    'date_range' => null, // No se filtra por fecha en este caso
                ];
                break;
            case 2:
                $filters = [
                    'year' => null, // No se filtra por año
                    'status' => $request->status ?? null,
                    'municipio' => null, // No se filtra por municipio en este caso
                    'date_range' => [$request->start_date, $request->end_date],
                ];
                break;
            case 3:
                $filters = [
                    'year' => null, // No se filtra por año
                    'status' => $request->status ?? null,
                    'municipio' => $request->municipality,
                    'date_range' => [$request->start_date, $request->end_date],
                ];
                break;
            case 4:
                $filters = [
                    'year' => $request->year, // No se filtra por año
                    'status' => $request->status ?? null,
                    'municipio' => $request->municipality,
                    'date_range' => null, // No se filtra por fecha en este caso
                ];
                break;
            default:
                abort(400, 'Tipo de reporte no válido');
        }

        return $this->getReport($filters);
    }

    /**
     * Genera el reporte basado en los filtros.
     */
    private function getReport(array $filters)
    {
        $status_name = null;
        if ($filters['status']) {
            $lost_status = LostStatus::find($filters['status']);
            $status_name = $lost_status->name ?? null;
        }

        // Obtener tipos de identificación
        $identifications = DocumentType::pluck('name', 'id')->map(fn($item) => strtolower($item));
        $identifications_legacy = TipoDocumento::pluck('DOCUMENTO', 'ID_TIPO_DOCUMENTO')->mapWithKeys(fn($item, $key) => [
            $key => match (strtolower($item)) {
                'credencial de elector', 'pasaporte', 'visa', 'licencia de conducir', 'otro documento' => strtolower($item),
                default => 'otro',
            }
        ]);

        // Obtener datos
        $report = $this->getData($filters, $identifications, $identifications_legacy);
        if (isset($filters['municipio'])) {
            $municipalities = $this->authApiService->getMunicipalities();
            $municipality = collect($municipalities)->firstWhere('id', $filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
        } else {
            $municipality_name = null;
        }

        $data = [
            'year' => $filters['year'],
            'data' => $report,
            'status_name' => $status_name,
            'municipality_name' => $municipality_name,
        ];

        Log::info('Reporte exportado por usuario: ' . Auth::id());
        return (new ExcelRequest())->create($data);
    }

    /**
     * Obtiene los datos de extravíos y documentos extraviados basado en los filtros.
     */
    private function getData(array $filters, $identifications, $identifications_legacy)
    {
        // Inicializar estructura de la tabla
        $currentYear = Carbon::now()->year;
        $maxMonths = ($filters['year'] == $currentYear) ? Carbon::now()->month : 12;
        $report = collect(range(1, $maxMonths))->mapWithKeys(fn($m) => [
            Carbon::create()->month($m)->format('F') => [
                'total_solicitudes' => 0,
                'identifications_count' => $identifications->mapWithKeys(fn($name) => [$name => 0])->toArray(),
            ]
        ])->toArray();


        $queryExtravios = Extravio::selectRaw('MONTH(PGJ_EXTRAVIOS.FECHA_REGISTRO) as mes, PGJ_OBJETOS.ID_TIPO_DOCUMENTO as document_type_id, COUNT(*) as total')
            ->join('PGJ_OBJETOS', 'PGJ_OBJETOS.ID_EXTRAVIO', '=', 'PGJ_EXTRAVIOS.ID_EXTRAVIO');

        $queryMisplacements = Misplacement::selectRaw('MONTH(misplacements.registration_date) as mes, ld.document_type_id, COUNT(*) as total')
            ->join('lost_documents as ld', 'misplacements.id', '=', 'ld.misplacement_id');

        if ($filters['year']) {
            $queryExtravios->whereYear('PGJ_EXTRAVIOS.FECHA_REGISTRO', $filters['year']);
            $queryMisplacements->whereYear('misplacements.registration_date', $filters['year']);
        }

        if ($filters['date_range']) {
            [$start, $end] = $filters['date_range'];
            $queryExtravios->whereBetween('PGJ_EXTRAVIOS.FECHA_REGISTRO', [$start, $end]);
            $queryMisplacements->whereBetween('misplacements.registration_date', [$start, $end]);
        }
        if ($filters['municipio']) {
            //$queryExtravios->where('PGJ_EXTRAVIOS.MUNICIPIO', $filters['municipio']);

            $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                ->where('place_events.municipality_api_id', $filters['municipio']);
        }

        if ($filters['status']) {
            $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', $filters['status']);
            $queryMisplacements->where('misplacements.lost_status_id', $filters['status']);
        }

        $queryExtravios->groupByRaw('MONTH(PGJ_EXTRAVIOS.FECHA_REGISTRO), PGJ_OBJETOS.ID_TIPO_DOCUMENTO');
        $queryMisplacements->groupByRaw('MONTH(misplacements.registration_date), ld.document_type_id');

        $extravios = $queryExtravios->get();
        $misplacements = $queryMisplacements->get();

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
        return $report;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function getByYear2(Request $request)
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

    public function createSurveys(Request $request)
    {
        return Inertia::render('Reports/CreateSurveys');
    }


    public function getSurveys(Request $request)
    {
        // Validar fechas
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Obtener encuestas en el rango de fechas
        $surveys = Survey::whereBetween('register_date', [$request->start_date, $request->end_date])->get();

        // Definir preguntas y sus claves en la base de datos
        $surveyQuestions = [
            'rating_1' => '¿Qué tan difícil fue ingresar al rubro de Extravío de Documentos?',
            'rating_2' => '¿Qué tan difícil le fue generar su Constancia?',
            'rating_3' => '¿Qué tan satisfecho se encuentra con el servicio?',
            'question_1' => '¿Fue útil la información brindada al inicio para el llenado de la Constancia?',
            'question_2' => '¿Solicitó ayuda telefónica?',
            'question_3' => '¿El servidor público le solicitó algún pago a cambio?',
            'question_4' => '¿Sintió discriminación en algún momento?',
        ];

        // Formatear datos para el Excel
        $formattedData = [];
        foreach ($surveyQuestions as $key => $question) {
            $responses = [];
            foreach ($surveys as $survey) {
                $responses[] = isset($survey[$key]) ? ($survey[$key] == 1 ? 'Sí' : 'No') : 'N/A';
            }

            $formattedData[] = [
                'question' => $question,
                'responses' => $responses
            ];
        }

        // Pasar los datos a la clase que genera el Excel
        $excelRequest = new ExcelSurvey();
        return $excelRequest->create($formattedData, $request->start_date, $request->end_date);
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
