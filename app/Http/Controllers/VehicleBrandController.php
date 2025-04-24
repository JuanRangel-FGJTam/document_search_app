<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\VehicleBrand;
use App\Models\VehicleSubBrand;

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

        $brand = VehicleBrand::create($validated);

        return redirect()->route('vehicleBrand.show',$brand->id)->with('success', 'Marca agregada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $brand = VehicleBrand::findOrFail($id);
        $brand->load('subBrands');
        return Inertia::render('Catalogs/VehicleBrands/Show', [
            'brand' => $brand,
        ]);
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

        return redirect()->route('vehicleBrand.show',$id)->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        //
        $brand = VehicleBrand::findOrFail($id);
        if ($brand->vehicles()->exists()) {
            return back()->withErrors(['error'=> 'No se puede eliminar esta marca porque está asociada a uno o más vehículos.']);
        }

        $brand->delete();

        return redirect()->back()->with('success', 'Marca eliminada correctamente.');
    }

    public function storeSubBrand(Request $request, string $brand_id)
    {
        $request->validate([
            'name' => 'required|unique:vehicle_sub_brands,name',
        ]);

        $RequestData = [
            'name' => $request->name,
            'vehicle_brand_id' => $brand_id,
        ];

        VehicleSubBrand::create($RequestData);

        return redirect()->back();
    }
}
