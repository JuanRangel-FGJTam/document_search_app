<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;
use Inertia\Inertia;
class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $totalType = \App\Models\VehicleType::orderBy('name', 'asc')
            ->get();
        $types = \App\Support\Pagination::paginate($totalType, $request);

        return Inertia::render('Catalogs/VehicleTypes/Index', [
            'types' => $types,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Catalogs/VehicleTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:vehicle_types,name',
        ]);

        $type = new \App\Models\VehicleType();
        $type->name = $request->name;
        $type->save();

        return redirect()->route('vehicleType.index')->with('success', 'Tipo de vehículo creado correctamente.');
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
        $type = VehicleType::findOrFail($id);
        return Inertia::render('Catalogs/VehicleTypes/Edit', [
            'type' => $type,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:vehicle_types,name,' . $id,
        ]);
        $type = VehicleType::findOrFail($id);
        $type->name = $request->name;
        $type->save();
        return redirect()->route('vehicleType.index')->with('success', 'Tipo de vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $type = \App\Models\VehicleType::findOrFail($id);
        if ($type->vehicles()->count() > 0) {
            return redirect()->route('vehicleModel.index')->with('error', 'No se puede eliminar el tipo de vehículo porque está asociado a vehículos.');
        }
        $type->delete();
        return redirect()->route('vehicleType.index')->with('success', 'Tipo de vehículo eliminado correctamente.');
    }
}
