<?php

namespace App\Http\Controllers;

use App\DataTables\VehicleDataTable;
use App\DataTables\VehicleTrashDataTable;
use App\Http\Requests\VehicleStoreRequest;
use App\Http\Requests\VehicleUpdateRequest;
use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return view('vehicles.index', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'table' => new VehicleDataTable,
        ]);
    }

    public function store(VehicleStoreRequest $request)
    {
        Vehicle::create($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('company');

        return view('vehicles.partials.show', compact('vehicle'));
    }

    public function update(Vehicle $vehicle, VehicleUpdateRequest $request)
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('vehicles.trash', ['table' => new VehicleTrashDataTable]);
    }

    public function restore(Vehicle $vehicle)
    {
        $vehicle->restore();

        return redirect()->route('vehicles.trash')->with('success', 'Kendaraan berhasil dipulihkan.');
    }

    public function forceDelete(Vehicle $vehicle)
    {
        $vehicle->forceDelete();

        return redirect()->route('vehicles.trash')->with('success', 'Kendaraan berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new VehicleDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new VehicleTrashDataTable)->responseFor($request);
    }
}
