<?php

namespace App\Http\Controllers;

use App\DataTables\VehicleTypeDataTable;
use App\Http\Requests\VehicleTypeStoreRequest;
use App\Http\Requests\VehicleTypeUpdateRequest;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        return view('vehicle-types.index', ['table' => new VehicleTypeDataTable]);
    }

    public function store(VehicleTypeStoreRequest $request)
    {
        VehicleType::create($request->validated());

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type berhasil ditambahkan.');
    }

    public function show(VehicleType $vehicleType)
    {
        return view('vehicle-types.partials.show', compact('vehicleType'));
    }

    public function update(VehicleType $vehicleType, VehicleTypeUpdateRequest $request)
    {
        $vehicleType->update($request->validated());

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type berhasil diperbarui.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type berhasil dihapus.');
    }

    public function getData(Request $request)
    {
        return (new VehicleTypeDataTable)->responseFor($request);
    }
}
