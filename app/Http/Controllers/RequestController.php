<?php

namespace App\Http\Controllers;

use App\Models\{
    CancellationReason,
    LostStatus,
    Misplacement
};

use App\Services\{
    AuthApiService,
    LostDocumentService,
    MisplacementLegacyService,
    MisplacementService,
    PlaceEventService
};
use App\Mail\EmailCancel;
use App\Mail\SendValidate;
use App\Models\Legacy\Extravio;
use App\Models\Legacy\Objeto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\{
    Auth,
    Log,
    Mail,
    Storage,
    Http,
    DB
};
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class RequestController extends Controller
{
    const LOST_STATUS_PENDING = 1;
    const LOST_STATUS_REVIEW = 2;
    const LOST_STATUS_ACCEPT = 3;
    const LOST_STATUS_CANCELATION = 4;
    const CATALOG_PHONE_TYPE_MOBILE = 1;
    const CATALOG_PHONE_TYPE_HOME = 2;
    const DOCUMENT_TYPE_INE = 1;

    const LAST_FOLIO_LEGACY = 163191;

    const LEGACY_MODE = 1;

    const LEGACY_URL = 'https://extraviodedocumentos.fgjtam.gob.mx/validacion/pagina/Validacion.aspx';
    const LOCAL_URL = 'https://extraviodedocumentos.fgjtam.gob.mx/validacion';

    protected  $misplacementService;
    protected $authApiService;
    protected $lostDocumentService;
    protected $placeEventService;

    public function __construct(MisplacementService $misplacementService, AuthApiService $authApiService, LostDocumentService $lostDocumentService, PlaceEventService $placeEventService)
    {
        $this->misplacementService = $misplacementService;
        $this->authApiService = $authApiService;
        $this->placeEventService = $placeEventService;
        $this->lostDocumentService = $lostDocumentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        // Obtener año y mes (usar actuales si no se proporcionan)
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('n'));

        $now = new \DateTime();
        $currentYear = $now->format('Y');
        $currentMonth = $now->format('n');

        $totalMisplacements = null;
        $lostStatuses = LostStatus::whereNotIn('id', [1, 2])->get();

        // Mapeo de los status válidos
        $statusMap = [
            SELF::LOST_STATUS_REVIEW => SELF::LOST_STATUS_REVIEW,
            SELF::LOST_STATUS_ACCEPT => SELF::LOST_STATUS_ACCEPT,
            SELF::LOST_STATUS_CANCELATION => SELF::LOST_STATUS_CANCELATION,
            SELF::LOST_STATUS_PENDING => SELF::LOST_STATUS_PENDING,
            5 => null // Si es 5, no se filtra por status
        ];

        $status_id = $statusMap[$status] ?? null;
        $query = Misplacement::query();

        // Aplicar filtro por mes y año
        $query->whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month);

        // Si se está buscando texto, realizar la búsqueda en Meilisearch
        if ($search !== null) {
            if ($search > SELF::LAST_FOLIO_LEGACY) {
                $totalMisplacements = Misplacement::search($search)->get();
                $totalMisplacements->load('people', 'lostStatus');
            } else {
                try {
                    $legacyMisplacements = Extravio::with('estadoExtravio')->where('ID_EXTRAVIO', $search)->get();
                    if ($legacyMisplacements->isNotEmpty()) {
                        $totalMisplacements = $legacyMisplacements->map(function ($legacy) {
                            return [
                                'id' => $legacy->ID_EXTRAVIO,
                                'document_number' => $legacy->ID_EXTRAVIO,
                                'lost_status_id' => $legacy->ID_ESTADO_EXTRAVIO,
                                'lost_status' => [
                                    'name' => $legacy->estadoExtravio->ESTADO_EXTRAVIO,
                                ],
                                'people' => [
                                    'name' => trim(($legacy->NOMBRE ?? '') . ' ' . ($legacy->PATERNO ?? '') . ' ' . ($legacy->MATERNO ?? '')),
                                ],
                                'registration_date' => $legacy->FECHA_EXTRAVIO,
                            ];
                        });
                    } else {
                        $totalMisplacements = collect();
                    }
                } catch (\Exception $e) {
                    Log::error("Error fetching legacy misplacements: " . $e->getMessage());
                    $totalMisplacements = collect();
                }
            }
        } else {
            // Si no hay búsqueda, aplicar el filtro de status
            if ($status_id !== null) {
                $query->where('lost_status_id', $status_id);
            }
            $totalMisplacements = $query->orderBy('document_number', 'desc')->get();
            $totalMisplacements->load('people', 'lostStatus');
        }

        // Generar lista de años y meses disponibles
        $years = range(2022, $currentYear);
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

        // Si el año seleccionado es el actual, limitar meses hasta el actual
        if ($year == $currentYear) {
            $months = array_slice($months, 0, $currentMonth, true);
        }

        // Paginación
        $misplacements = \App\Support\Pagination::paginate($totalMisplacements, $request);

        return Inertia::render('Requests/Index', [
            'misplacements' => $misplacements,
            'lost_statuses' => $lostStatuses,
            'totalMisplacements' => $totalMisplacements->count(),
            'years' => $years,
            'months' => $months,
            'currentYear' => $year,  // Pasar el año actual usado
            'currentMonth' => $month // Pasar el mes actual usado
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $misplacement_id)
    {
        //
        $person = null;
        $municipality = null;
        $colony = null;
        $misplacement = $this->misplacementService->getById($misplacement_id);
        if ($misplacement) {
            $personData = $this->authApiService->getPersonById($misplacement->people_id);
            $person = !empty($personData) ? $personData : null;
            $misplacement->load('lostStatus', 'cancellationReason', 'misplacementIdentifications.identificationType', 'user');
            $documents = $this->lostDocumentService->getByMisplacementId($misplacement_id);
            $documents->load('documentType');
            $placeEvent = $this->placeEventService->getByMisplacementId($misplacement_id);
            $placeEvent->lost_date = \Carbon\Carbon::parse($placeEvent->lost_date)->locale('es')->isoFormat('D [de] MMMM [del] YYYY');

            $identification = $this->authApiService->getDocumentById($misplacement->people_id, $misplacement->misplacementIdentifications->identification_type_id);

            $zipCodes = $this->authApiService->getZipCode($placeEvent->zipcode);
            $imageContent = Http::get($identification['fileUrl'])->body();
            $base64Image = base64_encode($imageContent);
            $identification['image'] = 'data:image/jpeg;base64,' . $base64Image;

            if ($identification['fileUrlBack']) {
                $imageContent = Http::get($identification['fileUrlBack'])->body();
                $base64Image = base64_encode($imageContent);
                $identification['imageBack'] = 'data:image/jpeg;base64,' . $base64Image;
            }

            if (isset($zipCodes['municipalities'])) {
                $municipality = collect($zipCodes['municipalities'])->firstWhere('default', 1);
            }
            if (isset($zipCodes['colonies'])) {
                $colony = collect($zipCodes['colonies'])->firstWhere('id', $placeEvent->colony_api_id);
            }
            $placeEvent['municipality'] = $municipality;
            $placeEvent['colony'] = $colony;
        } else {
            $extravio = Extravio::where('ID_EXTRAVIO', $misplacement_id)->first();
            $extravio->load('estadoExtravio', 'usuario', 'identificacion.cat_identificacion', 'tipoDocumento', 'motivoCancelacion', 'hechos', 'hechosCP');
            $person = null;
            if ($extravio->usuario && $extravio->usuario->idPersonApi) {
                $person = $this->authApiService->getPersonById($extravio->usuario->idPersonApi);
            }
            if (!$person) {
                $person = [
                    'fullName' => $extravio->NOMBRE . ' ' . $extravio->PATERNO . ' ' . $extravio->MATERNO,
                    'curp' => $extravio->identificacion->curprfc,
                    'genderName' => $extravio->identificacion->ID_SEXO === "1" ? "Masculino" : "Femenino",
                    'birthdateFormated' => $extravio->identificacion->FECHA_NACIMIENTO,
                    'age' => \Carbon\Carbon::parse($extravio->identificacion->FECHA_NACIMIENTO)->age,
                    'email' => $extravio->identificacion->CORREO_ELECTRONICO
                ];
            }
            $documentsData = Objeto::where('ID_EXTRAVIO', $misplacement_id)->get();
            $documentsData->load('tipoDocumento');

            $misplacement = [
                'id' => $extravio->ID_EXTRAVIO,
                'document_number' => $extravio->ID_EXTRAVIO,
                'lost_status_id' => $extravio->ID_ESTADO_EXTRAVIO, // Ajustar según sea necesario
                'lost_status' => [
                    'name' => $extravio->estadoExtravio->ESTADO_EXTRAVIO,
                ],
                'people' => [
                    'name' => trim(($extravio->NOMBRE ?? '') . ' ' . ($extravio->PATERNO ?? '') . ' ' . ($extravio->MATERNO ?? '')),
                ],
                'registration_date' => $extravio->FECHA_EXTRAVIO ?? null,
                'cancellation_date' => $extravio->FECHA_CANCELACION ?? null,
                'cancellation_reason_id' => $extravio->ID_MOTIVO_CANCELACION ?? null,
                'cancellation_reason' => [
                    'name' => $extravio->motivoCancelacion->MotivoCancelacion ?? null
                ],
                'cancellation_reason_description' => $extravio->OBSERVACIONES_CANCELACION ?? null,
                'misplacement_identifications' => [
                    'identification_type' => [
                        'name' => $extravio->identificacion->cat_identificacion->IDENTIFICACION ?? null
                    ],
                ],
            ];

            $identification = [
                'folio' => $extravio->identificacion->NUMERO_IDENTIFICACION ?? null,
                'image' => isset($extravio->identificacion->IDENTIFICACION)
                    ? 'data:image/jpeg;base64,' . base64_encode($extravio->identificacion->IDENTIFICACION)
                    : null
            ];

            $documents = $documentsData->map(function ($doc) {
                return [
                    'id' => $doc->ID_OBJETO,
                    'document_type' => [
                        'name' => $doc->tipoDocumento->DOCUMENTO
                    ],
                    'document_number' => $doc->NUMERO_DOCUMENTO,
                    'document_owner' => $doc->TITULAR_DOCUMENTO
                ];
            });

            $placeEvent = [
                'municipality' => [
                    'name' => $extravio->hechosCP->CPmunicipio ?? null
                ],
                'colony' => [
                    'name' => $extravio->hechosCP->CPcolonia ?? null
                ],
                'street' => $extravio->hechosCP->CPcalle ?? null,
                'lost_date' => $extravio->hechos->FECHA ?? null,
                'description' => $extravio->hechos->DESCRIPCION ?? null
            ];
        }


        return Inertia::render('Requests/Show', [
            'person' => $person,
            'misplacement' => $misplacement,
            'documents' => $documents,
            'placeEvent' => $placeEvent,
            'identification' => $identification
        ]);
    }

    public function reSendDocument(string $misplacement_id)
    {
        $person_name = null;
        $person_email = null;
        $misplacement = Misplacement::find($misplacement_id);
        $misplacementLegacyService = new MisplacementLegacyService();
        if (!$misplacement) {
            $personLegacy = $misplacementLegacyService->getPersonLegacy($misplacement_id);
            $person_name = $personLegacy['fullName'];
            $person_email = $personLegacy['email'];
            $fileURL = $this->regenerateLegacyPDF($misplacement_id);
        } else {
            $procedure = $this->authApiService->getProcedure($misplacement->people_id, $misplacement->document_api_id);
            if (!$procedure) {
                return redirect()->back()->with('error', 'La constancia no se ha encontrado. Ha pasado el plazo del almacenamiento del documento. Favor de cancelar esta constancia para la generación de una nueva.');
            }
            $person = $this->authApiService->getPersonById($misplacement->people_id);
            $person_name = $person['fullName'];
            $person_email = $person['email'];
            $url = $procedure['files'][0]['fileUrl'];
            $response = Http::get($url);
            if ($response->successful()) {
                $filename = 'document_' . $misplacement->people_id . '.pdf';
                $path = 'public/documents/' . $filename;
                Storage::put($path, $response->body());
                $fileURL =  'app/public/documents/' . $filename;
            }
        }

        $data = [
            "fullName" => $person_name,
            "folio" => (string) $misplacement_id,
            "status" => 'Constancia Generada',
            "area" => "Fiscalía Digital",
            "name" => "Constancia de Extravío de Documentos",
            "observations" => 'Reenvio de constancia a petición del usuario'
        ];

        Mail::to($person_email)->queue(new SendValidate($data, $fileURL));
        Log::debug("Success: Email Resend");
        return redirect()->back()->with('success', 'Constancia Reenviada Correctamente!');
    }


    public function attendRequest(string $misplacement_id)
    {
        $changeState = $this->changeMisplacementState($misplacement_id, SELF::LOST_STATUS_REVIEW);
        if ($changeState) {
            Log::info('Misplacement attended by user: ' . Auth::id() . ' misplacement ID: ' . $misplacement_id);
            return redirect()->back()->with('success', 'Misplacement attended successfully!');
        }
        Log::error('Error attending misplacement by user: ' . Auth::id() . ' misplacement ID: ' . $misplacement_id);
        return redirect()->back()->with('error', 'Misplacement not found');
    }


    public function cancelRequest(string $misplacement_id)
    {

        $cancellationReasons = CancellationReason::all();
        $now = new \DateTime();
        return Inertia::render('Requests/Cancel', [
            'cancellationReasons' => $cancellationReasons,
            'misplacement_id' => $misplacement_id,
            'today' => $now->format('Y-m-d')
        ]);
    }

    public function storeCancelRequest(Request $request, string $misplacement_id)
    {

        $request->validate([
            'deadline' => 'required|date',
            'cancellation_reason' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $misplacement = Misplacement::find($misplacement_id);
            $document_number = null;
            $person = null;
            $people_id = null;

            if ($misplacement) {
                $misplacement->update([
                    'lost_status_id' => SELF::LOST_STATUS_CANCELATION,
                    'cancellation_date' => $request->deadline,
                    'cancellation_reason_description' => $request->message,
                    'cancellation_reason_id' => $request->cancellation_reason,
                ]);
                $document_number = $misplacement->document_number;
                $people_id = $misplacement->people_id;
                $person = $this->authApiService->getPersonById($misplacement->people_id);
            } else {
                $legacyMisplacement = Extravio::where('ID_EXTRAVIO', $misplacement_id)->firstOrFail();
                $legacyMisplacement->load('estadoExtravio', 'usuario', 'identificacion', 'tipoDocumento', 'motivoCancelacion', 'hechos', 'hechosCP');
                $legacyMisplacement->ID_ESTADO_EXTRAVIO = SELF::LOST_STATUS_CANCELATION;
                $legacyMisplacement->FECHA_CANCELACION = $request->deadline;
                $legacyMisplacement->OBSERVACIONES_CANCELACION = $request->message;
                $legacyMisplacement->ID_MOTIVO_CANCELACION = $request->cancellation_reason;
                $legacyMisplacement->save();

                $document_number = $misplacement_id;
                $people_id = $legacyMisplacement->usuario->idPersonApi ?? null;
                $person = $people_id ? $this->authApiService->getPersonById($people_id) : null;
            }

            $reason = CancellationReason::findOrFail($request->cancellation_reason);
            if ($person) {
                $data = [
                    "fullName" => $person['fullName'] ?? 'Usuario',
                    "folio" => (string) $document_number,
                    "status" => $reason->name,
                    "area" => "Trámite en Línea",
                    "name" => "Constancia de Extravío de Documentos",
                    "observations" => $request->message ?? 'Sin observaciones',
                ];
                $this->authApiService->storeProcesure($people_id, $data);
                Mail::to($person['email'])->queue(new EmailCancel($data));
            }

            Log::info("Store Request Cancellation for Folio: " . $document_number . 'By user_id: ' . Auth::user()->id);

            DB::commit();
            return to_route('misplacement.show', $misplacement_id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in storeCancelRequest: " . $e->getMessage());
            return back()->withErrors(['message' => 'Ocurrió un error al procesar la solicitud.']);
        }
    }



    public function acceptRequest(string $misplacement_id)
    {
        $misplacement = $this->misplacementService->getById($misplacement_id);
        $now = new \DateTime();
        return Inertia::render('Requests/Validate', [
            'misplacement' => $misplacement,
            'today' => $now->format('Y-m-d')
        ]);
    }


    public function storeAcceptRequest(Request $request, string $misplacement_id)
    {
        $request->validate([
            'deadline' => 'required|date',
        ]);
        try {
            $fileURL = null;
            $misplacement = Misplacement::find($misplacement_id);
            $misplacement->validation_date = $request->deadline;
            $misplacement->lost_status_id = SELF::LOST_STATUS_ACCEPT;
            $misplacement->observations = $request->message;
            $misplacement->save();
            $person = $this->authApiService->getPersonById($misplacement->people_id);
            $data = [
                "fullName" => $person['fullName'] ?? 'Usuario',
                "folio" => (string) $misplacement->document_number,
                "status" => 'VÁLIDA',
                "area" => "Trámite en Línea",
                "name" => "Constancia de Extravío de Documentos",
                "observations" => $request->message ?? 'Sin observaciones'
            ];

            $this->authApiService->storeProcesure($misplacement->people_id, $data);


            $procedure = $this->authApiService->getProcedure($misplacement->people_id, $misplacement->document_api_id);
            $url = $procedure['files'][0]['fileUrl'];

            $response = Http::get($url);
            if ($response->successful()) {
                $filename = 'document_' . $misplacement->people_id . '.pdf';
                $path = 'public/documents/' . $filename;
                Storage::put($path, $response->body());
                $fileURL =  'app/public/documents/' . $filename;
            }

            Mail::to($person['email'])->queue(new SendValidate($data, $fileURL));

            return to_route('misplacement.show', $misplacement_id);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            Log::error("Error en storeData: " . $e->getMessage());
            return back()->withErrors(['message' => 'Ocurrió un error al procesar la solicitud.']);
        }
    }


    public function downloadPDF(string $misplacement_id)
    {
        $misplacement = Misplacement::find($misplacement_id);
        if (!$misplacement) {
            $fileURL = $this->regenerateLegacyPDF($misplacement_id);
        } else {
            $procedure = $this->authApiService->getProcedure($misplacement->people_id, $misplacement->document_api_id);
            if (!$procedure) {
                return redirect()->back()->with('error', 'La constancia no se ha encontrado. Ha pasado el plazo del almacenamiento del documento. Favor de cancelar esta constancia para la generación de una nueva.');
            }
            $url = $procedure['files'][0]['fileUrl'];
            $response = Http::get($url);
            if ($response->successful()) {
                $filename = 'document_' . $misplacement->people_id . '.pdf';
                $path = 'public/documents/' . $filename;
                Storage::put($path, $response->body());
                $fileURL =  'app/public/documents/' . $filename;
            } else {
                return redirect()->back()->with('error', 'Error al descargar el archivo. Intente nuevamente.');
            }
        }


        // Construir la ruta completa del archivo en el disco 'public'
        $path = storage_path($fileURL);
        // Verificar si el archivo existe
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        // Descargar el archivo
        return response()->download($path);
    }




    private function changeMisplacementState(string $misplacement_id, int $state_id)
    {
        $misplacement = Misplacement::find($misplacement_id);
        if ($misplacement) {
            $misplacement->lost_status_id = $state_id;
            $misplacement->save();
            Log::info('Misplacement state changed by user: ' . Auth::id() . ' misplacement ID: ' . $misplacement_id . ' to state: ' . $state_id);
            return [
                'message' => 'success',
                'id' => $misplacement->id
            ];
        }
        Log::error('Error changing ticket state by user: ' . Auth::id() . ' ticket ID: ' . $misplacement_id . ' to state: ' . $state_id);
        return false;
    }

    private function generatePdf($userResponse, $misplacement, $placeData, $document_lost, $identification, $localPath, $qrUrl)
    {
        $pdfData = [
            'folio' => $misplacement->document_number,
            'registration_date' => \Carbon\Carbon::parse($misplacement->registration_date)->locale('es')->isoFormat('D [de] MMMM [del] YYYY'),
            'fullName' => $userResponse['fullName'],
            'curp' => $userResponse['curp'],
            'genderName' => $userResponse['genderName'],
            'birthdateFormated' => $userResponse['birthdateFormated'],
            'email' => $userResponse['email'],
            'age' => $userResponse['age'],
            'address' => $userResponse['address'],
            'dataLost' => [
                'municipality' => $placeData['municipality_name'],
                'colonie' => $placeData['colony_name'],
                'street' => $placeData['street'],
                'description' => $placeData['description'],
                'lost_date' => \Carbon\Carbon::parse($placeData['lost_date'])->locale('es')->isoFormat('D [de] MMMM [del] YYYY'),
            ],
            'documentLost' => $document_lost,
            'identification' => $identification,
            'identificationPath' => $localPath,
            'qrUrl' => $qrUrl,
        ];

        $pdf = Dompdf::loadView('pdf.LostDocument', $pdfData);
        // Habilitar PHP para DOMPDF
        $pdf->getOptions()->setIsPhpEnabled(true);

        $pdf->render();
        $documentId = Str::uuid();
        Storage::disk('public')->put("tmp/$documentId.pdf", $pdf->output());

        return ['url' => Storage::url("tmp/$documentId.pdf"), 'document_name' => $documentId];
    }

    private function generateQrCode($url)
    {
        $renderer = new ImageRenderer(new RendererStyle(400), new ImagickImageBackEnd());
        $writer = new Writer($renderer);

        $randomId = Str::random(40);
        $filename = 'qrcode_' . $randomId . '.png';
        $path = 'public/qrCode/' . $filename;
        Storage::put($path, $writer->writeString($url));
        return 'storage/qrCode/' . $filename;
    }


    public function regenerateLegacyPDF(string $misplacement_id)
    {
        $misplacementLegacyService = new MisplacementLegacyService();
        $misplacement = Extravio::find($misplacement_id);
        $misplacementData = (object) [
            'document_number' => $misplacement_id,
            'registration_date' => $misplacement->FECHA_EXTRAVIO
        ];
        $personLegacy = $misplacementLegacyService->getPersonLegacy($misplacement_id);
        $placeDataLegacy = $misplacementLegacyService->getPlaceData($misplacement_id);
        $lostDocuments = $misplacementLegacyService->getLostDocuments($misplacement_id);
        $localPath = $personLegacy['identificacion']['file_path'];
        $url = SELF::LEGACY_URL . '?QueryStringFolio=' . $misplacement_id . '&QueryStringCodigo=' . $misplacement->CODIGO;
        $qrUrl = $this->generateQrCode($url);
        $identification = $personLegacy['identificacion'];
        $pdfUrl = $this->generatePdf($personLegacy, $misplacementData, $placeDataLegacy, $lostDocuments, $identification, $localPath, $qrUrl);
        Log::info('Regenerating legacy PDF for misplacement ID: ' . $misplacement_id);
        return 'app/public/tmp/' . $pdfUrl['document_name'] . '.pdf';
    }
}
