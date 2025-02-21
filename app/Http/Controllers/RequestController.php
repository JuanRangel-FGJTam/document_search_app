<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use App\Services\MisplacementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequestController extends Controller
{
    CONST LOST_STATUS_PENDING = 1;
    protected  $misplacementService;


    public function __construct(MisplacementService $misplacementService) {
        $this->misplacementService = $misplacementService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $misplacements = $this->misplacementService->getAllByStatusId(SELF::LOST_STATUS_PENDING);
        $misplacements->load('lostStatus');


        return Inertia::render('Dashboard',[
            'misplacements'=> $misplacements
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
