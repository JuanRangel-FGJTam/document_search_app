<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use App\Services\AuthApiService;
use App\Services\MisplacementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequestController extends Controller
{
    const LOST_STATUS_PENDING = 1;
    const CATALOG_PHONE_TYPE_MOBILE = 1;
    const CATALOG_PHONE_TYPE_HOME = 2;
    const DOCUMENT_TYPE_INE = 1;

    protected  $misplacementService;
    protected $authApiService;

    public function __construct(MisplacementService $misplacementService, AuthApiService $authApiService)
    {
        $this->misplacementService = $misplacementService;
        $this->authApiService = $authApiService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $totalMisplacements = $this->misplacementService->getAllByStatusId(SELF::LOST_STATUS_PENDING);
        $totalMisplacements->load('lostStatus');

        $misplacements = \App\Support\Pagination::paginate($totalMisplacements, $request);

        foreach ($misplacements as $key => $value) {
            $peopleData = $this->authApiService->getPersonById($value->people_id);
            $misplacements[$key]->fullName = $peopleData['fullName'] ?? 'N/A';
            $misplacements[$key]->email = $peopleData['email'] ?? 'N/A';
        }


        return Inertia::render('Requests/Index', [
            'misplacements' => $misplacements
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
        $misplacement->load('lostStatus','cancellationReason','misplacementIdentifications.identificationType');
        $person = $this->authApiService->getPersonById($misplacement->people_id);
        $personAddress = $this->authApiService->getUserLastAddress($misplacement->people_id);
        $personPhoneHome = $this->authApiService->getUserContactType($misplacement->people_id, self::CATALOG_PHONE_TYPE_HOME);
        $personPhoneMobile = $this->authApiService->getUserContactType($misplacement->people_id, self::CATALOG_PHONE_TYPE_MOBILE);

        return Inertia::render('Requests/Show',[
            'person'=>$person,
            'misplacement'=>$misplacement,
            'personAddress'=>$personAddress,
            'personPhoneHome'=>$personPhoneHome,
            'personPhoneMobile'=>$personPhoneMobile
        ]);
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
