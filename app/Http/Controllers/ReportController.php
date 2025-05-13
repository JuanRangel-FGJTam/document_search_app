<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
use App\Models\{
    DocumentType,
    LostStatus,
    ReportType,
    Survey,
    VehicleBrand,
    VehicleSubBrand,
    VehicleType
};
use App\Models\Legacy\Encuesta;
use App\Models\Legacy\Extravio;
use App\Models\Legacy\TipoDocumento;
use App\Services\AuthApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    protected $authApiService;
    const OTHER_DOCUMENT = 9;
    const OTHER_DOCUMENT_LEGACY = 5;
    const OTHER_DOCUMENT_LEGACY_2  = 6;
    const ALL_STATUS = 3;
    const LAST_FOLIO_LEGACY = 163191;

    const LOCAL_PASAPORT_ID = 5;
    const LOCAL_LICENSE_ID = 7;
    const LEGACY_PASAPORT_ID = 2;
    const LEGACY_LICENSE_ID = 4;

    const REPORT_TYPE_PLATE = 1;

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
        $document_types = DocumentType::all();
        return Inertia::render('Reports/CreateByYear', [
            'years' => $years,
            'months' => $months,
            'lost_statuses' => $lost_statuses,
            'report_types' => $report_types,
            'municipalities' => $municipalities,
            'document_types' => $document_types,
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
        $is_plate_report = false;
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
                    'document_type' => $request->document_type,
                    'keyword' => $request->keyword,
                ];
                break;
            case 3:
                $filters = [
                    'year' => null, // No se filtra por año
                    'status' => $request->status ?? null,
                    'municipio' => $request->municipality,
                    'date_range' => [$request->start_date, $request->end_date],
                    'document_type' => $request->document_type,
                    'keyword' => $request->keyword,
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
            case 6:
                $filters = [
                    'year' => null, // No se filtra por año
                    'status' => $request->status ?? null,
                    'date_range' => [$request->start_date, $request->end_date],
                    'download' => $request->download ?? null,
                    'vehicle_type' => $request->vehicle_type ?? null,
                ];
                return $this->getPlateReport($filters);
                break;
            case 7:
                $filters = [
                    'year' => $request->year,
                    'status' => $request->status ?? null,
                    'municipio' => $request->municipality,
                    'download' => $request->download ?? null,
                    'vehicle_type' => $request->vehicle_type ?? null,
                ];
                return $this->getPlateReport($filters);
                break;
            case 8:
                $filters = [
                    'year' => $request->year,
                    'status' => $request->status ?? null,
                    'municipio' => $request->municipality,
                    'vehicle_type' => $request->vehicle_type,
                    'download' => $request->download ?? null,
                ];
                return $this->getPlateReport($filters);
                break;
            default:
                abort(400, 'Tipo de reporte no válido');
        }
        $filters['download'] = $request->download ?? null;
        if ($is_plate_report) {
            return $this->getPlateReport($filters);
        }
        return $this->getReport($filters);
    }

    public function getPlateReport(array $filters)
    {
        $status_name = null;
        $municipality_name = null;
        $vechileBrands = null;
        if ($filters['status']) {
            $lost_status = LostStatus::find($filters['status']);
            $status_name = $lost_status->name ?? null;
        }

        if (isset($filters['municipio'])) {
            $municipality = $this->authApiService->getMunicipalityById($filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
        }

        if (isset($filters['vehicle_type'])) {
            $vehicle_type = VehicleType::find($filters['vehicle_type']);
            $vehicle_type_name = $vehicle_type->name ?? null;
            $subBrands = VehicleSubBrand::where('vehicle_type_id', $filters['vehicle_type'])->pluck('vehicle_brand_id')->unique();
            $vechileBrands = VehicleBrand::whereIn('id', $subBrands)->pluck('name', 'id')->map(fn($item) => strtolower($item));
        } else {
            $vechileBrands = VehicleBrand::pluck('name', 'id')->map(fn($item) => strtolower($item));
        }

        if ($filters['download']) {
            if (isset($filters['date_range']) && !empty($filters['date_range'])) {
                $data = $this->generateExcelPlateReport($filters);
                $document_type_name = 'Placas';
                $keyword = null;
                Log::info('Reporte generada por usuario: ' . Auth::id());
                return (new ExcelForDays())->create($data, $status_name, $municipality_name, $document_type_name, $keyword, $filters['date_range'][0], $filters['date_range'][1]);
            } else {
                // Obtener datos
                $report = $this->getPlateData($filters, $vechileBrands);
                $data = [
                    'year' => $filters['year'],
                    'data' => $report,
                    'status_name' => $status_name,
                    'municipality_name' => $municipality_name,
                    'plate_document' => 'Marca de Vehículo'
                ];
                Log::info('Reporte exportado por usuario: ' . Auth::id());
                return (new ExcelRequest())->create($data);
            }
        }
    }

    public function getPlateData(array $filters, $brands)
    {
        // Inicializar estructura de la tabla
        $currentYear = Carbon::now()->year;
        $maxMonths = ($filters['year'] == $currentYear) ? Carbon::now()->month : 12;
        $report = collect(range(1, $maxMonths))->mapWithKeys(fn($m) => [
            Carbon::create()->month($m)->format('F') => [
                'total_solicitudes' => 0,
                'identifications_count' => $brands->mapWithKeys(fn($name) => [$name => 0])->toArray(),
            ]
        ])->toArray();

        $queryMisplacements = Misplacement::selectRaw('MONTH(misplacements.registration_date) as mes, v.vehicle_brand_id, COUNT(*) as total')
            ->join('vehicles as v', 'misplacements.id', '=', 'v.misplacement_id');

        if ($filters['year']) {
            $queryMisplacements->whereYear('misplacements.registration_date', $filters['year']);
        }

        if ($filters['municipio']) {
            $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                ->where('place_events.municipality_api_id', $filters['municipio']);
        }

        if($filters['vehicle_type']) {
            $queryMisplacements->where('v.vehicle_type_id', $filters['vehicle_type']);
        }

        if ($filters['status']) {
            $queryMisplacements->where('misplacements.lost_status_id', $filters['status']);
        }

        $queryMisplacements->groupByRaw('MONTH(misplacements.registration_date), v.vehicle_brand_id');

        $misplacements = $queryMisplacements->get();

        // Se coloca el nombre del id guardado anteriormente
        foreach ($misplacements as $item) {
            $mes = Carbon::create()->month((int) $item->mes)->format('F');
            $identification_name = $brands[$item->vehicle_brand_id];
            $report[$mes]['identifications_count'][$identification_name] += $item->total;
            $report[$mes]['total_solicitudes'] += $item->total;
        }


        // Filtrar identifications_count para dejar solo los que sean diferentes de 0
        foreach ($report as $mes => &$data) {
            $data['identifications_count'] = array_filter(
                $data['identifications_count'],
                fn($count) => $count != 0
            );
        }
        unset($data);
        return $report;
    }


    /**
     * Genera el reporte basado en los filtros.
     */
    private function getReport(array $filters)
    {
        $status_name = null;
        $municipality_name = null;
        $document_type_name = null;
        $keyword = null;
        if ($filters['status']) {
            $lost_status = LostStatus::find($filters['status']);
            $status_name = $lost_status->name ?? null;
        }

        if (isset($filters['municipio'])) {
            $municipality = $this->authApiService->getMunicipalityById($filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
        }
        if (isset($filters['keyword'])) {
            $keyword = $filters['keyword'];
        }
        if (isset($filters['document_type'])) {
            $document_type = DocumentType::find($filters['document_type']);
            $document_type_name = $document_type->name ?? null;
            if ($filters['document_type'] != SELF::OTHER_DOCUMENT) {
                $keyword = null;
            }
        }

        $identifications = DocumentType::pluck('name', 'id')->map(fn($item) => strtolower($item));
        $identifications_legacy = TipoDocumento::pluck('DOCUMENTO', 'ID_TIPO_DOCUMENTO')->mapWithKeys(fn($item, $key) => [
            $key => match (strtolower($item)) {
                'credencial de elector', 'pasaporte', 'visa', 'licencia de conducir', 'otro documento' => strtolower($item),
                default => 'otro',
            }
        ]);

        if ($filters['download']) {
            if (isset($filters['date_range']) && !empty($filters['date_range'])) {
                $data = $this->generateExcelReport($filters);
                Log::info('Reporte generada por usuario: ' . Auth::id());
                return (new ExcelForDays())->create($data, $status_name, $municipality_name, $document_type_name, $keyword, $filters['date_range'][0], $filters['date_range'][1]);
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

        if (isset($filters['date_range']) && !empty($filters['date_range'])) {
            $data = $this->generateExcelReport($filters);

            $startDate = \Carbon\Carbon::parse($filters['date_range'][0]);
            $endDate = isset($filters['date_range'][1]) && $filters['date_range'][1]
                ? \Carbon\Carbon::parse($filters['date_range'][1])
                : now(); // si no hay fecha de fin, se toma hoy

            $monthsInRange = [];
            $cursor = $startDate->copy()->startOfMonth();

            while ($cursor->lte($endDate)) {
                $monthsInRange[$cursor->format('F')] = 0;
                $cursor->addMonth();
            }

            $monthTranslations = [
                'January' => 'Enero',
                'February' => 'Febrero',
                'March' => 'Marzo',
                'April' => 'Abril',
                'May' => 'Mayo',
                'June' => 'Junio',
                'July' => 'Julio',
                'August' => 'Agosto',
                'September' => 'Septiembre',
                'October' => 'Octubre',
                'November' => 'Noviembre',
                'December' => 'Diciembre',
            ];

            // Inicializamos con los meses que sí están en el rango
            $totalPerMonth = [];
            foreach ($monthsInRange as $monthEnglish => $value) {
                $totalPerMonth[$monthTranslations[$monthEnglish]] = 0;
            }

            $totalGeneral = 0;

            foreach ($data as $date => $count) {
                if ($date === 'total_general') continue;

                try {
                    $dateObj = \Carbon\Carbon::parse($date);
                    $monthEnglish = $dateObj->format('F');

                    if (isset($monthsInRange[$monthEnglish])) {
                        $monthSpanish = $monthTranslations[$monthEnglish];
                        $totalPerMonth[$monthSpanish] += $count;
                        $totalGeneral += $count;
                    }
                } catch (\Exception $e) {
                    Log::warning("Fecha inválida en el reporte: $date");
                }
            }

            Log::info('Gráfica generada por rango de fechas por el usuario: ' . Auth::id());

            return response()->json([
                'totalPerMonth' => $totalPerMonth,
                'totalPerIdentification' => $document_type_name
                    ? [$document_type_name => $totalGeneral]
                    : ['Total' => $totalGeneral],
                'municipality_name' => $municipality_name,
                'document_type_name' => $document_type_name,
                'date_range' => [
                    'start' => $filters['date_range'][0],
                    'end' => $filters['date_range'][1] ?? now()->toDateString(),
                ],
            ]);
        } else {
            // Obtener datos
            $report = $this->getData($filters, $identifications, $identifications_legacy);
            $totalPerIdentification = [];
            $totalPerMonth = [];

            foreach ($report as $month => $data) {
                // Total de solicitudes por mes
                $monthInSpanish = match ($month) {
                    'January' => 'Enero',
                    'February' => 'Febrero',
                    'March' => 'Marzo',
                    'April' => 'Abril',
                    'May' => 'Mayo',
                    'June' => 'Junio',
                    'July' => 'Julio',
                    'August' => 'Agosto',
                    'September' => 'Septiembre',
                    'October' => 'Octubre',
                    'November' => 'Noviembre',
                    'December' => 'Diciembre',
                    default => $month,
                };
                $totalPerMonth[$monthInSpanish] = $data['total_solicitudes'];

                // Inicializa los tipos de identificaciones si es la primera vez
                foreach ($data['identifications_count'] as $type => $quantity) {
                    if (!isset($totalPerIdentification[$type])) {
                        $totalPerIdentification[$type] = 0;
                    }
                    $totalPerIdentification[$type] += $quantity;
                }
            }
            Log::info('Grafica generada por usuario: ' . Auth::id());
            return response()->json([
                'year' => $filters['year'],
                'totalPerMonth' => $totalPerMonth,
                'totalPerIdentification' => $totalPerIdentification,
                'municipality_name' => $municipality_name,
            ]);
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

            $municipality = $this->authApiService->getMunicipalityById($filters['municipio']);
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


    private function generateExcelPlateReport(array $filters)
    {
        // Validar rango de fechas
        if (empty($filters['date_range'])) {
            Log::error('Error: A date range is required.');
            throw new \Exception('A date range is required.');
        }

        [$start, $end] = $filters['date_range'];
        $dates = collect();

        if ($start && !$end) {
            Log::info('Generating dates: Only start date provided.', ['start' => $start]);
            $dates->put(Carbon::parse($start)->format('Y-m-d'), 0); // Only start
        } elseif (!$start && $end) {
            Log::info('Generating dates: Only end date provided.', ['end' => $end]);
            $dates->put(Carbon::parse($end)->format('Y-m-d'), 0); // Only end
        } elseif ($start && $end) {
            Log::info('Generating dates: Full range provided.', ['start' => $start, 'end' => $end]);
            for ($date = Carbon::parse($start); $date->lte(Carbon::parse($end)); $date->addDay()) {
                $dates->put($date->format('Y-m-d'), 0); // Initialize to 0
            }
        }

        Log::info('Generated dates:', ['dates' => $dates]);

        $queryMisplacements = Misplacement::selectRaw('DATE(misplacements.registration_date) as fecha, COUNT(*) as total')
            ->join('vehicles as v', 'misplacements.id', '=', 'v.misplacement_id');

        if ($start && $end) {
            Log::info('Applying date range filter.', ['start' => $start, 'end' => $end]);
            $queryMisplacements->whereBetween('misplacements.registration_date', [$start, $end]);
        } elseif ($start) {
            Log::info('Applying start date filter.', ['start' => $start]);
            $queryMisplacements->whereDate('misplacements.registration_date', $start);
        } elseif ($end) {
            Log::info('Applying end date filter.', ['end' => $end]);
            $queryMisplacements->whereDate('misplacements.registration_date', $end);
        }


        Log::info('Grouping queries by date.');
        $queryMisplacements->groupByRaw('DATE(misplacements.registration_date)');

        // Aplicar filtros (municipio y estado)
        if (!empty($filters['municipio'])) {
            if (!empty($filters['municipio'])) {
                $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                    ->where('place_events.municipality_api_id', $filters['municipio']);
            }
        } else {
            $queryMisplacements->leftJoin('place_events', 'misplacements.id', '=', 'place_events.misplacement_id');
        }


        if (!empty($filters['status'])) {
            $queryMisplacements->where('misplacements.lost_status_id', $filters['status']);
        }

        $misplacements = $queryMisplacements->get();
        // Procesar resultados y calcular totales
        $grandTotal = 0;

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



    private function generateExcelReport(array $filters)
    {
        // Validar rango de fechas
        if (empty($filters['date_range'])) {
            Log::error('Error: A date range is required.');
            throw new \Exception('A date range is required.');
        }

        [$start, $end] = $filters['date_range'];
        $dates = collect();

        if ($start && !$end) {
            Log::info('Generating dates: Only start date provided.', ['start' => $start]);
            $dates->put(Carbon::parse($start)->format('Y-m-d'), 0); // Only start
        } elseif (!$start && $end) {
            Log::info('Generating dates: Only end date provided.', ['end' => $end]);
            $dates->put(Carbon::parse($end)->format('Y-m-d'), 0); // Only end
        } elseif ($start && $end) {
            Log::info('Generating dates: Full range provided.', ['start' => $start, 'end' => $end]);
            for ($date = Carbon::parse($start); $date->lte(Carbon::parse($end)); $date->addDay()) {
                $dates->put($date->format('Y-m-d'), 0); // Initialize to 0
            }
        }

        Log::info('Generated dates:', ['dates' => $dates]);

        // Queries (grouped by date only)
        $queryExtravios = Extravio::selectRaw(
            "CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120) as fecha,
            COUNT(*) as total"
        )
            ->join('PGJ_OBJETOS', 'PGJ_OBJETOS.ID_EXTRAVIO', '=', 'PGJ_EXTRAVIOS.ID_EXTRAVIO');

        $queryMisplacements = Misplacement::selectRaw('DATE(misplacements.registration_date) as fecha, COUNT(*) as total')
            ->join('lost_documents as ld', 'misplacements.id', '=', 'ld.misplacement_id');

        if ($start && $end) {
            Log::info('Applying date range filter.', ['start' => $start, 'end' => $end]);
            $queryExtravios->whereBetween(
                DB::raw("CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120)"),
                [Carbon::parse($start)->format('Y-m-d'), Carbon::parse($end)->format('Y-m-d')]
            );
            $queryMisplacements->whereBetween('misplacements.registration_date', [$start, $end]);
        } elseif ($start) {
            Log::info('Applying start date filter.', ['start' => $start]);
            $queryExtravios->whereDate(
                DB::raw("CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120)"),
                Carbon::parse($start)->format('Y-m-d')
            );
            $queryMisplacements->whereDate('misplacements.registration_date', $start);
        } elseif ($end) {
            Log::info('Applying end date filter.', ['end' => $end]);
            $queryExtravios->whereDate(
                DB::raw("CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120)"),
                Carbon::parse($end)->format('Y-m-d')
            );
            $queryMisplacements->whereDate('misplacements.registration_date', $end);
        }

        if ($filters['document_type']) {
            Log::info('Applying document type filter.', ['document_type' => $filters['document_type']]);
            if ($filters['document_type'] == SELF::OTHER_DOCUMENT) {
                Log::info('Document type filter: Other document.', ['keyword' => $filters['keyword'] ?? null]);
                $queryExtravios->where('PGJ_OBJETOS.ID_TIPO_DOCUMENTO', SELF::OTHER_DOCUMENT_LEGACY_2);
                if (!empty($filters['keyword'])) {
                    $queryExtravios->where('PGJ_OBJETOS.ESPECIFIQUE', 'LIKE', '%' . $filters['keyword'] . '%');
                }
            } else {
                if ($filters['document_type'] == SELF::LOCAL_PASAPORT_ID) {
                    $queryExtravios->where('PGJ_OBJETOS.ID_TIPO_DOCUMENTO', SELF::LEGACY_PASAPORT_ID);
                } elseif ($filters['document_type'] == SELF::LOCAL_LICENSE_ID) {
                    $queryExtravios->where('PGJ_OBJETOS.ID_TIPO_DOCUMENTO', SELF::LEGACY_LICENSE_ID);
                } else {
                    $queryExtravios->where('PGJ_OBJETOS.ID_TIPO_DOCUMENTO', $filters['document_type']);
                }
            }
            $queryMisplacements->where('ld.document_type_id', $filters['document_type']);
            if ($filters['document_type'] == SELF::OTHER_DOCUMENT && !empty($filters['keyword'])) {
                $queryMisplacements->where('ld.specification', 'LIKE', '%' . $filters['keyword'] . '%');
            }
        }

        Log::info('Grouping queries by date.');
        $queryExtravios->groupBy(DB::raw("CONVERT(varchar(10), PGJ_EXTRAVIOS.FECHA_EXTRAVIO, 120)"));
        $queryMisplacements->groupByRaw('DATE(misplacements.registration_date)');

        // Aplicar filtros (municipio y estado)
        if (!empty($filters['municipio'])) {
            $municipality = $this->authApiService->getMunicipalityById($filters['municipio']);
            $municipality_name = $municipality['name'] ?? null;
            if (!empty($municipality_name)) {
                $queryExtravios
                    ->join('PGJ_HECHOS_CP', 'PGJ_EXTRAVIOS.ID_EXTRAVIO', '=', 'PGJ_HECHOS_CP.ID_EXTRAVIO')
                    ->where('PGJ_HECHOS_CP.CPmunicipio', 'LIKE', '%' . $municipality_name . '%');
            }
            // Solo si se obtuvo un ID válido
            if (!empty($filters['municipio'])) {
                $queryMisplacements->join('place_events', 'misplacements.id', '=', 'place_events.misplacement_id')
                    ->where('place_events.municipality_api_id', $filters['municipio']);
            }
        } else {
            // Si municipio está vacío, no se aplica ningún filtro y se toman todos los municipios
            $queryExtravios->leftJoin('PGJ_HECHOS_CP', 'PGJ_EXTRAVIOS.ID_EXTRAVIO', '=', 'PGJ_HECHOS_CP.ID_EXTRAVIO');
            $queryMisplacements->leftJoin('place_events', 'misplacements.id', '=', 'place_events.misplacement_id');
        }


        if (!empty($filters['status'])) {
            if ($filters['status'] == 3) {
                $queryExtravios->where('PGJ_EXTRAVIOS.ID_ESTADO_EXTRAVIO', '<=', SELF::ALL_STATUS);
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
                $municipality = $this->authApiService->getMunicipalityById($survey->misplacement->placeEvent->municipality_api_id);
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


    public function indexReportPlates(Request $request)
    {
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
        $report_types = ReportType::whereIn('id', [6, 7, 8])->get();
        $document_types = DocumentType::all();
        $vehicle_types = VehicleType::all();

        return Inertia::render('PlateReports/Index', [
            'years' => $years,
            'months' => $months,
            'lost_statuses' => $lost_statuses,
            'report_types' => $report_types,
            'municipalities' => $municipalities,
            'document_types' => $document_types,
            'vehicle_types' => $vehicle_types,
        ]);
    }
}
