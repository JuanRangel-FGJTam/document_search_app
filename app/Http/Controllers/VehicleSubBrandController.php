<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleSubBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $totalSubBrand = \App\Models\VehicleSubBrand::with('brand')->orderBy('name', 'asc')
            ->get();
        $subBrands = \App\Support\Pagination::paginate($totalSubBrand, $request);

        return Inertia::render('Catalogs/VehicleSubBrands/Index', [
            'subBrands' => $subBrands,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $brands = \App\Models\VehicleBrand::orderBy('name', 'asc')->get();
        return Inertia::render('Catalogs/VehicleSubBrands/Create', [
            'brands' => $brands,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_sub_brands,name',
            'brand' => 'required|exists:vehicle_brands,id',
        ]);
        $subBrand = new \App\Models\VehicleSubBrand();
        $subBrand->name = $request->name;
        $subBrand->vehicle_brand_id = $request->brand;
        $subBrand->save();
        return redirect()->route('vehicleSubBrand.index')->with('success', 'Sub-brand created successfully.');
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
    public function edit(Request $request, string $id)
    {
        //
        $view = $request->query('view');
        $subBrand = \App\Models\VehicleSubBrand::findOrFail($id);
        $brands = \App\Models\VehicleBrand::orderBy('name', 'asc')->get();
        return Inertia::render('Catalogs/VehicleSubBrands/Edit', [
            'subBrand' => $subBrand,
            'brands' => $brands,
            'view' => $view,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $view = $request->query('view');
        $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_sub_brands,name,' . $id,
            'brand' => 'required|exists:vehicle_brands,id',
        ]);
        $subBrand = \App\Models\VehicleSubBrand::findOrFail($id);
        $subBrand->name = $request->name;
        $subBrand->vehicle_brand_id = $request->brand;
        $subBrand->save();

        if($view == 'vehicleBrand.show'){
            return redirect()->route('vehicleBrand.show', $subBrand->vehicle_brand_id)->with('success', 'Sub-brand updated successfully.');
        }

        return redirect()->route('vehicleSubBrand.index')->with('success', 'Sub-brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        //
        $view = $request->query('view');
        $subBrand = \App\Models\VehicleSubBrand::findOrFail($id);
        /*
        if ($subBrand->vehicles()->count() > 0) {
            return redirect()->route('vehicleSubBrand.index')->with('error', 'Cannot delete sub-brand with associated vehicles or brands.');
        }
            */
        $subBrand->delete();

        if($view == 'vehicleBrand.show'){
            return redirect()->route('vehicleBrand.show', $subBrand->vehicle_brand_id)->with('success', 'Sub-brand deleted successfully.');
        }
        return redirect()->route('vehicleSubBrand.index')->with('success', 'Sub-brand deleted successfully.');
    }
}
