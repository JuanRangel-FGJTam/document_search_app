<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $totalModel = \App\Models\VehicleModel::orderBy('name', 'asc')
            ->get();
        $models = \App\Support\Pagination::paginate($totalModel, $request);

        return Inertia::render('Catalogs/VehicleModels/Index', [
            'models' => $models,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Catalogs/VehicleModels/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:vehicle_models,name',
        ]);

        $model = new \App\Models\VehicleModel();
        $model->name =strtoupper($request->name);
        $model->save();

        return redirect()->route('vehicleModel.index')->with('success', 'Modelo de vehículo creado correctamente.');
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
        $model = \App\Models\VehicleModel::findOrFail($id);
        return Inertia::render('Catalogs/VehicleModels/Edit', [
            'model' => $model,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'name' => 'required|string|unique:vehicle_models,name,' . $id,
        ]);
        $model = \App\Models\VehicleModel::findOrFail($id);
        $model->name = strtoupper($request->name);
        $model->save();
        return redirect()->route('vehicleModel.index')->with('success', 'Modelo de vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $model = \App\Models\VehicleModel::findOrFail($id);
        if ($model->vehicles()->count() > 0) {
            return redirect()->route('vehicleModel.index')->withErrors(['error'=> 'No se puede eliminar el modelo de vehículo porque está asociado a vehículos.']);
        }
        $model->delete();
        return redirect()->route('vehicleModel.index')->with('success', 'Modelo de vehículo eliminado correctamente.');
    }
}
