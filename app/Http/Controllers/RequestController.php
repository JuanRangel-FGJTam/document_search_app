<?php

namespace App\Http\Controllers;

use App\Mail\AcceptRequest;
use App\Mail\CancelRequest;
use App\Models\CancellationReason;
use App\Models\LostStatus;
use App\Models\Misplacement;
use App\Services\AuthApiService;
use App\Services\LostDocumentService;
use App\Services\MisplacementService;
use App\Services\PlaceEventService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $lostStatuses = LostStatus::all();

        // Si no hay status en la query, por defecto mostrar solo las solicitudes pendientes
        if ($status === null) {
            $status = SELF::LOST_STATUS_PENDING;
        }

        // Mapeo de los status válidos
        $statusMap = [
            SELF::LOST_STATUS_REVIEW => SELF::LOST_STATUS_REVIEW,
            SELF::LOST_STATUS_ACCEPT => SELF::LOST_STATUS_ACCEPT,
            SELF::LOST_STATUS_CANCELATION => SELF::LOST_STATUS_CANCELATION,
            SELF::LOST_STATUS_PENDING => SELF::LOST_STATUS_PENDING,
            5 => null // Si es 5, no se filtra por status
        ];

        $status_id = $statusMap[$status] ?? null;

        // Construcción de la consulta
        $query = Misplacement::with('lostStatus');

        if ($status_id !== null) {
            $query->where('lost_status_id', $status_id);
        }

        $totalMisplacements = $query->get();
        // Paginación
        $misplacements = \App\Support\Pagination::paginate($totalMisplacements, $request);

        // Obtener datos adicionales
        foreach ($misplacements as $key => $value) {
            $peopleData = $this->authApiService->getPersonById($value->people_id);
            $misplacements[$key]->fullName = $peopleData['fullName'] ?? 'N/A';
            $misplacements[$key]->email = $peopleData['email'] ?? 'N/A';
        }

        return Inertia::render('Requests/Index', [
            'misplacements' => $misplacements,
            'lost_statuses' => $lostStatuses,
            'totalMisplacements'=> $totalMisplacements->count()
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
        $misplacement = $this->misplacementService->getById($misplacement_id);
        $misplacement->load('lostStatus', 'cancellationReason', 'misplacementIdentifications.identificationType');
        $person = $this->authApiService->getPersonById($misplacement->people_id);
        //$personAddress = $this->authApiService->getUserLastAddress($misplacement->people_id);
        //$personPhoneHome = $this->authApiService->getUserContactType($misplacement->people_id, self::CATALOG_PHONE_TYPE_HOME);
        //$personPhoneMobile = $this->authApiService->getUserContactType($misplacement->people_id, self::CATALOG_PHONE_TYPE_MOBILE);

        $documents = $this->lostDocumentService->getByMisplacementId($misplacement_id);
        $documents->load('documentType');

        $placeEvent = $this->placeEventService->getByMisplacementId($misplacement_id);
        $placeEvent->lost_date = \Carbon\Carbon::parse($placeEvent->lost_date)->locale('es')->isoFormat('D [de] MMMM [del] YYYY');

        $zipCodes = $this->authApiService->getZipCode($placeEvent->zipcode);

        if (empty($zipCodes['municipalities']) || empty($zipCodes['colonies'])) {
            return response()->json(['error' => 'Zip code not found'], 404);
        }

        $municipality = collect($zipCodes['municipalities'])->firstWhere('default', 1);
        $colony = collect($zipCodes['colonies'])->firstWhere('id', $placeEvent->colony_api_id);
        $placeEvent['municipality'] = $municipality;
        $placeEvent['colony'] = $colony;
        return Inertia::render('Requests/Show', [
            'person' => $person,
            'misplacement' => $misplacement,
            //'personAddress' => $personAddress,
            //'personPhoneHome' => $personPhoneHome,
            //'personPhoneMobile' => $personPhoneMobile,
            'documents' => $documents,
            'placeEvent' => $placeEvent,
        ]);
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
        $misplacement = $this->misplacementService->getById($misplacement_id);
        $now = new \DateTime();
        return Inertia::render('Requests/Cancel', [
            'cancellationReasons' => $cancellationReasons,
            'misplacement' => $misplacement,
            'today' => $now->format('Y-m-d')
        ]);
    }

    public function storeCancelRequest(Request $request, string $misplacement_id)
    {

        $request->validate([
            'deadline' => 'required|date',
            'cancellation_reason' => 'required',
        ]);

        $misplacement = Misplacement::find($misplacement_id);
        $misplacement->lost_status_id = SELF::LOST_STATUS_CANCELATION;
        $misplacement->cancellation_date = $request->deadline;
        $misplacement->cancellation_reason_description = $request->message;
        $misplacement->cancellation_reason_id = $request->cancellation_reason;
        $misplacement->save();

        $reason = CancellationReason::find($request->cancellation_reason);

        $data = [
            "folio" => (string) $misplacement->id,
            "status" => $reason->name,
            "area" => "Trámite en Línea",
            "name" => "Constancia de Extravío de Documentos",
            "observations" => $request->message ?? 'Sin observaciones'
        ];

        $this->authApiService->storeProcesure($misplacement->people_id, $data);

        $person = $this->authApiService->getPersonById($misplacement->people_id);

        Mail::to($person['email'])->queue(new CancelRequest($data));

        return to_route('misplacement.show', $misplacement_id);
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

        $misplacement = Misplacement::find($misplacement_id);
        $misplacement->lost_status_id = SELF::LOST_STATUS_ACCEPT;
        $misplacement->description = $request->message;
        $misplacement->save();

        $data = [
            "folio" => (string) $misplacement->id,
            "status" => 'VÁLIDA',
            "area" => "Trámite en Línea",
            "name" => "Constancia de Extravío de Documentos",
            "observations" => $request->message ?? 'Sin observaciones'
        ];

        $this->authApiService->storeProcesure($misplacement->people_id, $data);

        $person = $this->authApiService->getPersonById($misplacement->people_id);

        Mail::to($person['email'])->queue(new AcceptRequest($data));

        return to_route('misplacement.show',$misplacement_id);

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
