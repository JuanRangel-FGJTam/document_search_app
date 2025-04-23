<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\VehicleBrand;
class VehicleBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $totalBrands = \App\Models\VehicleBrand::orderBy('name', 'asc')
            ->get();
        $brands = \App\Support\Pagination::paginate($totalBrands, $request);

        return Inertia::render('Catalogs/VehicleBrands/Index', [
            'brands' => $brands,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Catalogs/VehicleBrands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|unique:vehicle_brands,name',
        ]);

        VehicleBrand::create($validated);

        return redirect()->route('vehicleBrand.index')->with('success', 'Marca agregada correctamente.');
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
        $brand = VehicleBrand::findOrFail($id);
        return Inertia::render('Catalogs/VehicleBrands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|unique:vehicle_brands,name,' . $id,
        ]);
        $brand = VehicleBrand::findOrFail($id);
        $brand->update($validated);

        return redirect()->route('vehicleBrand.index')->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        //
        $brand = VehicleBrand::findOrFail($id);
        if ($brand->vehicles()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar esta marca porque está asociada a uno o más vehículos.',
            ], 422);
        }

        $brand->delete();

        return redirect()->back()->with('success', 'Marca eliminada correctamente.');
    }
}
