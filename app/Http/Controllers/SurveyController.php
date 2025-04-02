<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('n'));

        $now = new \DateTime();
        $currentYear = $now->format('Y');
        $currentMonth = $now->format('n');

        // Aplicar filtro por mes y año
        $totalSurveys = Survey::with('misplacement.people')
            ->whereYear('register_date', $year)
            ->whereMonth('register_date', $month)->get();;
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
        $surveys = \App\Support\Pagination::paginate($totalSurveys, $request);

        return inertia('Surveys/Index', [
            'surveys' => $surveys,
            'totalSurveys' => $totalSurveys->count(),
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
    public function show(string $survey_id)
    {
        //
        $survey = Survey::with('misplacement.people')->find($survey_id);

        return inertia('Surveys/Show', [
            'survey' => $survey,
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
