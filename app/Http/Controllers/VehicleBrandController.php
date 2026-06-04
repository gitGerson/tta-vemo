<?php

namespace App\Http\Controllers;

use App\DataTables\VehicleBrandDataTable;
use App\Http\Requests\VehicleBrandStoreRequest;
use App\Http\Requests\VehicleBrandUpdateRequest;
use App\Models\VehicleBrand;
use App\Services\FilePondUploadService;
use Illuminate\Http\Request;

class VehicleBrandController extends Controller
{
    public function __construct(protected FilePondUploadService $filePond) {}

    public function index()
    {
        return view('vehicle-brands.index', ['table' => new VehicleBrandDataTable]);
    }

    public function store(VehicleBrandStoreRequest $request)
    {
        $data = $request->validated();
        $data['logo'] = $this->filePond->storeFromRequestField($request, 'logo', 'vehicle_brand_logos', 's3');

        VehicleBrand::create($data);

        return redirect()->route('vehicle-brands.index')->with('success', 'Vehicle brand berhasil ditambahkan.');
    }

    public function show(VehicleBrand $vehicleBrand)
    {
        return view('vehicle-brands.partials.show', compact('vehicleBrand'));
    }

    public function update(VehicleBrandUpdateRequest $request, VehicleBrand $vehicleBrand)
    {
        $data = $request->validated();
        $data['logo'] = $this->filePond->storeFromRequestField($request, 'logo', 'vehicle_brand_logos', 's3') ?? $vehicleBrand->logo;

        $vehicleBrand->update($data);

        return redirect()->route('vehicle-brands.index')->with('success', 'Vehicle brand berhasil diperbarui.');
    }

    public function destroy(VehicleBrand $vehicleBrand)
    {
        $vehicleBrand->delete();

        return redirect()->route('vehicle-brands.index')->with('success', 'Vehicle brand berhasil dihapus.');
    }

    public function getData(Request $request)
    {
        return (new VehicleBrandDataTable)->responseFor($request);
    }
}
