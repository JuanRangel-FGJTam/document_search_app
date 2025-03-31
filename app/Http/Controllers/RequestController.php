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
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Extract;

class RequestController extends Controller
{
    const LOST_STATUS_PENDING = 1;
    const LOST_STATUS_REVIEW = 2;
    const LOST_STATUS_ACCEPT = 3;
    const LOST_STATUS_CANCELATION = 4;
    const CATALOG_PHONE_TYPE_MOBILE = 1;
    const CATALOG_PHONE_TYPE_HOME = 2;
    const DOCUMENT_TYPE_INE = 1;

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

        // Si se está buscando texto, realizar la búsqueda en Meilisearch
        if ($search !== null) {
            $totalMisplacements = Misplacement::search($search)->get();
            if ($totalMisplacements->isEmpty()) {
                try {
                    $legacyMisplacements = Extravio::with('estadoExtravio')->where('ID_EXTRAVIO', $search)->get();
                    if ($legacyMisplacements->isNotEmpty()) {
                        $totalMisplacements = $legacyMisplacements->map(function ($legacy) {
                            return [
                                'id' => $legacy->ID_EXTRAVIO,
                                'document_number' => $legacy->ID_EXTRAVIO,
                                'lost_status_id' => $legacy->ID_ESTADO_EXTRAVIO, // Ajustar según sea necesario
                                'lost_status' => [
                                    'name' => $legacy->estadoExtravio->ESTADO_EXTRAVIO,
                                ],
                                'people' => [
                                    'name' => trim(($legacy->NOMBRE ?? '') . ' ' . ($legacy->PATERNO ?? '') . ' ' . ($legacy->MATERNO ?? '')),
                                ],
                                'registration_date' => $legacy->FECHA_EXTRAVIO, // Ajustar según sea necesario
                            ];
                        });
                    } else {
                        $totalMisplacements = collect(); // Retorna vacío si no se encuentran resultados
                    }
                } catch (\Exception $e) {
                    Log::error("Error fetching legacy misplacements: " . $e->getMessage());
                    $totalMisplacements = collect(); // Retorna vacío si ocurre un error
                }
            } else {
                $totalMisplacements->load('people', 'lostStatus');
            }
        } else {
            // Si no hay búsqueda, aplicar el filtro de status
            if ($status_id !== null) {
                $query->where('lost_status_id', $status_id);
            }
            $totalMisplacements = $query->orderBy('document_number', 'desc')->get();
            $totalMisplacements->load('people', 'lostStatus');
        }

        // Paginación
        $misplacements = \App\Support\Pagination::paginate($totalMisplacements, $request);

        return Inertia::render('Requests/Index', [
            'misplacements' => $misplacements,
            'lost_statuses' => $lostStatuses,
            'totalMisplacements' => $totalMisplacements->count()
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
            $misplacement->load('lostStatus', 'cancellationReason', 'misplacementIdentifications.identificationType');
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
            $extravio->load('estadoExtravio', 'usuario', 'identificacion', 'tipoDocumento', 'motivoCancelacion', 'hechos', 'hechosCP');
            $person = null;
            if ($extravio->usuario && $extravio->usuario->idPersonApi) {
                $person = $this->authApiService->getPersonById($extravio->usuario->idPersonApi);
            }
            if (!$person) {
                $person = [
                    'fullName' => $extravio->NOMBRE . ' ' . $extravio->PATERNO . ' ' . $extravio->MATERNO,
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
                'registration_date' => $extravio->FECHA_EXTRAVIO,
                'cancellation_date' => $extravio->FECHA_CANCELACION,
                'cancellation_reason_id' => $extravio->ID_MOTIVO_CANCELACION ?? null,
                'cancellation_reason' => [
                    'name' => $extravio->motivoCancelacion->MotivoCancelacion ?? null
                ],
                'cancellation_reason_description' => $extravio->OBSERVACIONES_CANCELACION,
                'misplacement_identifications' => [
                    'identification_type' => $extravio->identificacion->IDENTIFICACION
                ],
            ];

            $identification = [
                'folio' => $extravio->NUMERO_DOCUMENTO
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
        $misplacement = Misplacement::find($misplacement_id);
        $procedure = $this->authApiService->getProcedure($misplacement->people_id, $misplacement->document_api_id);
        if (!$procedure) {
            return redirect()->back()->with('error', 'La constancia no se ha encontrado. Ha pasado el plazo del almacenamiento del documento. Favor de comunicarle al usuario que realice de nuevo el trámite.');
        }
        $person = $this->authApiService->getPersonById($misplacement->people_id);
        $url = $procedure['files'][0]['fileUrl'];
        $response = Http::get($url);
        if ($response->successful()) {
            $filename = 'document_' . $misplacement->people_id . '.pdf';
            $path = 'public/documents/' . $filename;
            Storage::put($path, $response->body());
            $fileURL =  'app/public/documents/' . $filename;
        }

        $data = [
            "fullName" => $person['fullName'],
            "folio" => (string) $misplacement->id,
            "status" => 'Constancia Generada',
            "area" => "Fiscalía Digital",
            "name" => "Constancia de Extravío de Documentos",
            "observations" => 'Reenvio de Constancia'
        ];

        Mail::to($person['email'])->queue(new SendValidate($data, $fileURL));
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

            Log::info("Store Request Cancellation for Folio: " . $document_number. 'By user_id: '. Auth::user()->id);

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
        $procedure = $this->authApiService->getProcedure($misplacement->people_id, $misplacement->document_api_id);
        if (!$procedure) {
            return redirect()->back()->with('error', 'La constancia no se ha encontrado. Ha pasado el plazo del almacenamiento del documento. Favor de comunicarle al usuario que realice de nuevo el trámite.');
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
