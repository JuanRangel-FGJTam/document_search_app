<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ExcelRequest;
class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $currentYear = date('Y');
        $currentMonth = date('n');
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

        if ($request->query('year') == $currentYear) {
            $months = array_slice($months, 0, $currentMonth, true);
        }
        return Inertia::render('Reports/Index', [
            'years' => $years,
            'months' => $months
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
    public function getByYear(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric'
        ]);

        // Obtener las solicitudes del año seleccionado con las identificaciones y los nombres de los tipos de identificación
        $misplacements = Misplacement::with('misplacementIdentifications.identificationType')
            ->whereYear('registration_date', $request->year)
            ->get();

        // Agrupar las solicitudes por mes
        $misplacementsGroupedByMonth = $misplacements->groupBy(function ($misplacement) {
            return Carbon\Carbon::parse($misplacement->registration_date)->format('F'); // Agrupar por nombre del mes
        });

        // Contar los tipos de identificación por mes con nombres
        $report = $misplacementsGroupedByMonth->map(function ($items) {
            // Obtener todos los tipos de identificación relacionados
            $identifications = $items->flatMap(function ($misplacement) {
                // Verificar si 'misplacementIdentifications' tiene algún valor
                if ($misplacement->misplacementIdentifications) {
                    // Acceder a 'identificationType' directamente, ya que la relación es HasOne
                    return [$misplacement->misplacementIdentifications->identificationType->name ?? 'Desconocido'];
                }
                return []; // Retornar un array vacío si no hay identificaciones
            });

            // Depuración para ver los resultados

            // Contar los tipos de identificación agrupados por nombre
            return [
                'total_solicitudes' => $items->count(),
                'identifications_count' => $identifications->groupBy(function ($name) {
                    return $name; // Agrupar por nombre del tipo de identificación
                })
                    ->map(function ($group) {
                        return $group->count(); // Contar la cantidad de cada tipo de identificación
                    })
            ];
        });

        $data = [
            'year'=> $request->year,
            'data'=> $report
        ];

        $excel = new ExcelRequest();
        Log::info('Emails exported by user: ' . Auth::id());
        return $excel->create($data);

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
