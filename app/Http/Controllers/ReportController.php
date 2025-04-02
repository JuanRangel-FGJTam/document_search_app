<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
use App\Models\DocumentType;
use App\Models\IdentificationType;
use App\Models\Legacy\Encuesta;
use App\Models\Legacy\Extravio;
use App\Models\Legacy\Identificacion;
use App\Models\Legacy\Objeto;
use App\Models\Legacy\TipoDocumento;
use App\Models\LostStatus;
use App\Models\ReportType;
use App\Models\Survey;
use App\Services\AuthApiService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        if (isset($filters['municipio'])) {
            $municipalities = $this->authApiService->getMunicipalities();
            $municipality = collect($municipalities)->firstWhere('id', $filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
        } else {
            $municipality_name = null;
        }
        $identifications = DocumentType::pluck('name', 'id')->map(fn($item) => strtolower($item));
        $identifications_legacy = TipoDocumento::pluck('DOCUMENTO', 'ID_TIPO_DOCUMENTO')->mapWithKeys(fn($item, $key) => [
            $key => match (strtolower($item)) {
                'credencial de elector', 'pasaporte', 'visa', 'licencia de conducir', 'otro documento' => strtolower($item),
                default => 'otro',
            }
        ]);

        if (isset($filters['date_range']) && !empty($filters['date_range'])) {
            $data = $this->generateExcelReport($filters, $identifications, $identifications_legacy);
            Log::info('Reporte exportado por usuario: ' . Auth::id());
            return (new ExcelForDays())->create($data, $status_name, $municipality_name, $filters['date_range'][0], $filters['date_range'][1]);
        } else {
            // Obtener datos
            $report = $this->getData($filters, $identifications, $identifications_legacy);
            $data = [
                'year' => $filters['year'],
                'data' => $report,
                'status_name' => $status_name,
                'municipality_name' => $municipality_name,
            ];
            Log::info('Reporte exportado por usuario: ' . Auth::id());
            return (new ExcelRequest())->create($data);
        }
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

            $municipalities = $this->authApiService->getMunicipalities();
            $municipality = collect($municipalities)->firstWhere('id', $filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;

            if ($municipality_name) {
                $queryExtravios->join('PGJ_HECHOS_CP', 'PGJ_EXTRAVIOS.ID_EXTRAVIO', '=', 'PGJ_HECHOS_CP.ID_EXTRAVIO')->where('PGJ_HECHOS_CP.CPmunicipio', 'LIKE', '%'  . $municipality_name . '%');
            }

            $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                ->where('place_events.municipality_api_id', $filters['municipio']);
        }

        if ($filters['status']) {
            if ($filters['status'] == 3) {
                $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', '<=', 3);
            } else {
                $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', $filters['status']);
            }
            $queryMisplacements->where('misplacements.lost_status_id', $filters['status']);
        }

        $queryExtravios->groupByRaw('MONTH(PGJ_EXTRAVIOS.FECHA_REGISTRO), PGJ_OBJETOS.ID_TIPO_DOCUMENTO');
        $queryMisplacements->groupByRaw('MONTH(misplacements.registration_date), ld.document_type_id');

        $extravios = $queryExtravios->get();
        $misplacements = $queryMisplacements->get();
        //dd($identifications_legacy);
        // Se coloca el nombre del id guardado anteriormente
        foreach ($extravios as $item) {
            $mes = Carbon::create()->month((int) $item->mes)->format('F');
            $identification_name = $identifications_legacy[$item->document_type_id] ?? 'otro';

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

    private function generateExcelReport(array $filters, $identifications, $identifications_legacy)
    {
        // Validar rango de fechas
        if (empty($filters['date_range'])) {
            throw new \Exception('Se requiere un rango de fechas.');
        }

        [$start, $end] = $filters['date_range'];
        $dates = collect();
        for ($date = Carbon::parse($start); $date->lte(Carbon::parse($end)); $date->addDay()) {
            $dates->put($date->format('Y-m-d'), 0); // Inicializar en 0
        }

        // Consultas (solo agrupadas por fecha)
        $queryExtravios = Extravio::selectRaw(
            "CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120) as fecha,
            COUNT(*) as total"
        )
            ->join('PGJ_OBJETOS', 'PGJ_OBJETOS.ID_EXTRAVIO', '=', 'PGJ_EXTRAVIOS.ID_EXTRAVIO')
            ->whereBetween(
                DB::raw("CONVERT(date, PGJ_EXTRAVIOS.FECHA_EXTRAVIO)"),
                [Carbon::parse($start)->format('Y-m-d'), Carbon::parse($end)->format('Y-m-d')]
            )
            ->groupBy(DB::raw("CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120)"));

        $queryMisplacements = Misplacement::selectRaw('DATE(misplacements.registration_date) as fecha, COUNT(*) as total')
            ->join('lost_documents as ld', 'misplacements.id', '=', 'ld.misplacement_id')
            ->whereBetween('misplacements.registration_date', [$start, $end])
            ->groupByRaw('DATE(misplacements.registration_date)');

        // Aplicar filtros (municipio y estado)
        if (!empty($filters['municipio'])) {
            $municipalities = $this->authApiService->getMunicipalities();
            $municipality = collect($municipalities)->firstWhere('id', $filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
            if ($municipality_name) {
                $queryExtravios->join('PGJ_HECHOS_CP', 'PGJ_EXTRAVIOS.ID_EXTRAVIO', '=', 'PGJ_HECHOS_CP.ID_EXTRAVIO')
                    ->where('PGJ_HECHOS_CP.CPmunicipio', 'LIKE', '%' . $municipality_name . '%');
            }

            $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                ->where('place_events.municipality_api_id', $filters['municipio']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] == 3) {
                $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', '<=', 3);
            } else {
                $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', $filters['status']);
            }
            $queryMisplacements->where('misplacements.lost_status_id', $filters['status']);
        }

        // Ejecutar consultas
        try {
            $extravios = $queryExtravios->get();
        } catch (\PDOException $e) {
            Log::error("Error en consulta de extravíos: " . $e->getMessage());
            $extravios = collect();
        }

        $misplacements = $queryMisplacements->get();

        // Procesar resultados y calcular totales
        $grandTotal = 0;

        foreach ($extravios as $item) {
            if ($dates->has($item->fecha)) {
                $dates[$item->fecha] += $item->total;
                $grandTotal += $item->total;
            }
        }

        foreach ($misplacements as $item) {
            if ($dates->has($item->fecha)) {
                $dates[$item->fecha] += $item->total;
                $grandTotal += $item->total;
            }
        }

        // Añadir el gran total como último elemento (clave "total_general")
        $dates->put('total_general', $grandTotal);
        return $dates;
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
        $surveys = Survey::with('misplacement.placeEvent')->whereBetween('register_date', [$request->start_date, $request->end_date])->get();

        // Definir preguntas y sus claves en la base de datos
        $surveyQuestions = [
            'rating_1' => '¿Qué tan difícil fue ingresar al rubro de Extravío de Documentos?',
            'question_1' => '¿Fue útil la información brindada al inicio para el llenado de la Constancia?',
            'rating_2' => '¿Qué tan difícil le fue generar su Constancia?',
            'rating_3' => '¿Qué tan satisfecho se encuentra con el servicio?',
            'question_2' => '¿Solicitó ayuda telefónica?',
            'question_3' => '¿El servidor público le solicitó algún pago a cambio?',
            'question_4' => '¿Al realizar el trámite sintió discriminación en algún momento?',
            'question_5' => 'No tengo sugerencias',
            'questions_6' => 'Reducir el número de requisitos',
            'question_7' => 'Formatos más sencillos',
            'question_8' => 'Información más específica'
        ];

        // Formatear datos para el Excel
        $formattedData = [];
        foreach ($surveys as $survey) {
            $row = [];
            foreach ($surveyQuestions as $key => $question) {
                if (str_starts_with($key, 'question')) {
                    $response = isset($survey[$key]) ? ($survey[$key] == 1 ? 'Sí' : ($survey[$key] == 0 ? 'No' : ' ')) : ' ';
                } else {
                    $response = isset($survey[$key]) ? $survey[$key] : ' ';
                }
                $row[$question] = $response;
            }

            $documentNumber = $survey->misplacement->document_number ?? ' ';
            $person = $this->authApiService->getPersonById($survey->misplacement->people_id);
            if (!$person) {
                continue;
            }
            $municipality_address = $person['address']['municipalityName'] ?? ' ';

            if (isset($survey->misplacement->placeEvent->municipality_api_id)) {
                $municipalities = $this->authApiService->getMunicipalities();
                $municipality = collect($municipalities)->firstWhere('id', $survey->misplacement->placeEvent->municipality_api_id);
                $municipality_event = $municipality['name'] ?? null;
            } else {
                $municipality_event = null;
            }

            $registration_date = $survey->misplacement->registration_date;
            $mx_registration_date = $registration_date ? Carbon::parse($registration_date)->format('d/m/Y') : ' ';

            $row['Folio'] = $documentNumber;
            $row['Municipio Hechos'] = $municipality_event;
            $row['Municipio Domicilio'] = $municipality_address;
            $row['Fecha Registro'] = $mx_registration_date;

            $formattedData[] = $row;
        }
        /*
        $surveysLegacy = Encuesta::whereRaw("ISDATE(fechaRegistro) = 1") // Filtra solo las fechas válidas
            ->whereRaw("CAST(fechaRegistro AS DATETIME) BETWEEN ? AND ?", [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->with(['extravio.hechosCP', 'extravio.domicilioCP'])
            ->get();
*/
        try {
            $surveysLegacy = Encuesta::whereRaw("ISDATE(fechaRegistro) = 1")
                ->whereRaw("CONVERT(varchar(8),fechaRegistro, 112) BETWEEN ? AND ?", [
                    Carbon::parse($request->start_date)->format('Ymd'),
                    Carbon::parse($request->end_date)->format('Ymd')
                ])
                ->with(['extravio.hechosCP', 'extravio.domicilioCP'])
                ->get();
        } catch (\PDOException $e) {
            // Maneja la excepción
            Log::error('Error en la consulta: ' . $e->getMessage());
            // Aquí puedes retornar algún mensaje o realizar otras acciones, como un fallback.


            // Aquí podrías hacer una consulta alternativa o devolver un resultado vacío
            $surveysLegacy = collect(); // Si no se puede obtener, devolver una colección vacía
        }


        foreach ($surveysLegacy as $survey) {
            $row = [];
            foreach ($surveyQuestions as $key => $question) {
                $legacyKey = match ($key) {
                    'rating_1' => 'Rating1',
                    'rating_2' => 'Rating3',
                    'rating_3' => 'Rating4',
                    'question_1' => 'rbl2',
                    'question_2' => 'rbl5',
                    'question_3' => 'rbl6',
                    'question_4' => 'rbl7',
                    'question_5' => 'chbNotengo',
                    'questions_6' => 'chbReducirReq',
                    'question_7' => 'chbFormatosSen',
                    'question_8' => 'chbInformacionEspe',
                    default => null,
                };

                if (str_starts_with($key, 'question')) {
                    if ($key == 'question_5') {
                        $response = isset($survey[$legacyKey])
                            ? ($survey[$legacyKey] == 1 ? 'X' : ($survey[$legacyKey] == 2 ? ' ' : ' '))
                            : ' ';
                    } else {
                        $response = isset($survey[$legacyKey])
                            ? ($survey[$legacyKey] == 1 ? 'Sí' : ($survey[$legacyKey] == 2 ? 'No' : ' '))
                            : ' ';
                    }
                } else {
                    $response = isset($survey[$legacyKey]) ? $survey[$legacyKey] : ' ';
                }
                $row[$question] = $response;
            }

            $documentNumber = $survey->IdExtravio ?? 'N/A';
            $extravio = Extravio::with('domicilioCP', 'hechosCP')->where('ID_EXTRAVIO', $survey->IdExtravio)->first();
            // Obtener el people_id desde el usuario relacionado con el extravio
            $peopleId = isset($extravio) && $extravio->idUsuario
                ? $this->authApiService->getPersonById($extravio->idUsuario) ?? null
                : null;

            if ($peopleId) {
                $person = $this->authApiService->getPersonById($peopleId);
                $municipality_address = $person['address']['municipalityName'] ?? ' ';
            } else {
                // Si no se encuentra el usuario, obtener el domicilio desde DOMICILIOCP
                $domicilio = $extravio->domicilioCP ?? null;
                $municipality_address = $domicilio && $domicilio->CPmunicipio ? $domicilio->CPmunicipio : ' ';
            }

            if (isset($extravio->hechosCP->CPmunicipio)) {
                $municipality_event = $extravio->hechosCP->CPmunicipio;
            } else {
                $municipality_event = null;
            }

            $registration_date = $survey->fechaRegistro ?? ' ';
            $mx_registration_date = $registration_date ? Carbon::parse($registration_date)->format('d/m/Y') : ' ';

            $row['Folio'] = $documentNumber;
            $row['Municipio Hechos'] = $municipality_event;
            $row['Municipio Domicilio'] = $municipality_address;
            $row['Fecha Registro'] = $mx_registration_date;

            $formattedData[] = $row;
        }

        // Asegurarse de que 'Folio' esté primero en cada fila
        $formattedData = array_map(function ($row) {
            $folio = $row['Folio'];
            unset($row['Folio']);
            return array_merge(['Folio' => $folio], $row);
        }, $formattedData);

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
