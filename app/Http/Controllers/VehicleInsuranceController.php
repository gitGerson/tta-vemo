<?php

namespace App\Http\Controllers;

use App\DataTables\VehicleInsuranceDataTable;
use App\DataTables\VehicleInsuranceSnapshotDataTable;
use App\DataTables\VehicleInsuranceTrashDataTable;
use App\Http\Requests\VehicleInsuranceStoreRequest;
use App\Http\Requests\VehicleInsuranceUpdateRequest;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Http\Request;

class VehicleInsuranceController extends Controller
{
    public function index()
    {
        return view('vehicle-insurances.index', [
            'table' => new VehicleInsuranceDataTable,
        ]);
    }

    public function create(Request $request)
    {
        return view('vehicle-insurances.create', [
            'vehicles' => $this->vehicleOptions(),
            'selectedVehicleId' => $request->integer('vehicle_id') ?: null,
        ]);
    }

    public function store(VehicleInsuranceStoreRequest $request)
    {
        VehicleInsurance::create($request->validated());

        return redirect()->route('vehicle-insurances.index')->with('success', 'Data asuransi berhasil ditambahkan.');
    }

    public function show(VehicleInsurance $vehicleInsurance)
    {
        $vehicleInsurance->load('vehicle');

        return view('vehicle-insurances.show', [
            'insurance' => $vehicleInsurance,
            'snapshotTable' => new VehicleInsuranceSnapshotDataTable($vehicleInsurance),
        ]);
    }

    public function edit(VehicleInsurance $vehicleInsurance)
    {
        return view('vehicle-insurances.edit', [
            'insurance' => $vehicleInsurance->load('vehicle'),
            'vehicles' => $this->vehicleOptions(),
        ]);
    }

    public function update(VehicleInsurance $vehicleInsurance, VehicleInsuranceUpdateRequest $request)
    {
        $vehicleInsurance->update($request->validated());

        return redirect()->route('vehicle-insurances.index')->with('success', 'Data asuransi berhasil diperbarui.');
    }

    public function destroy(VehicleInsurance $vehicleInsurance)
    {
        $vehicleInsurance->delete();

        return redirect()->route('vehicle-insurances.index')->with('success', 'Data asuransi berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('vehicle-insurances.trash', ['table' => new VehicleInsuranceTrashDataTable]);
    }

    public function restore(VehicleInsurance $vehicleInsurance)
    {
        $vehicleInsurance->restore();

        return redirect()->route('vehicle-insurances.trash')->with('success', 'Data asuransi berhasil dipulihkan.');
    }

    public function forceDelete(VehicleInsurance $vehicleInsurance)
    {
        $vehicleInsurance->forceDelete();

        return redirect()->route('vehicle-insurances.trash')->with('success', 'Data asuransi berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new VehicleInsuranceDataTable)->responseFor($request);
    }

    public function getDataForVehicle(Vehicle $vehicle, Request $request)
    {
        return (new VehicleInsuranceDataTable($vehicle))->responseFor($request);
    }

    public function getSnapshotData(VehicleInsurance $vehicleInsurance, Request $request)
    {
        return (new VehicleInsuranceSnapshotDataTable($vehicleInsurance))->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new VehicleInsuranceTrashDataTable)->responseFor($request);
    }

    private function vehicleOptions()
    {
        return Vehicle::query()->orderBy('plat_new')->get(['id', 'plat_new', 'type']);
    }
}
