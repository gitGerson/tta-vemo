<?php

namespace App\Http\Controllers;

use App\DataTables\FuelTypeDataTable;
use App\DataTables\FuelTypeTrashDataTable;
use App\Http\Requests\FuelTypeStoreRequest;
use App\Http\Requests\FuelTypeUpdateRequest;
use App\Models\FuelType;
use Illuminate\Http\Request;

class FuelTypeController extends Controller
{
    public function index()
    {
        return view('fuel-types.index', ['table' => new FuelTypeDataTable]);
    }

    public function store(FuelTypeStoreRequest $request)
    {
        FuelType::create($request->validated());

        return redirect()->route('fuel-types.index')->with('success', 'Jenis BBM berhasil ditambahkan.');
    }

    public function show(FuelType $fuelType)
    {
        return view('fuel-types.partials.show', compact('fuelType'));
    }

    public function update(FuelType $fuelType, FuelTypeUpdateRequest $request)
    {
        $fuelType->update($request->validated());

        return redirect()->route('fuel-types.index')->with('success', 'Jenis BBM berhasil diperbarui.');
    }

    public function destroy(FuelType $fuelType)
    {
        $fuelType->delete();

        return redirect()->route('fuel-types.index')->with('success', 'Jenis BBM berhasil dihapus.');
    }

    public function trash()
    {
        return view('fuel-types.trash', ['table' => new FuelTypeTrashDataTable]);
    }

    public function restore(FuelType $fuelType)
    {
        $fuelType->restore();

        return redirect()->route('fuel-types.trash')->with('success', 'Jenis BBM berhasil dipulihkan.');
    }

    public function forceDelete(FuelType $fuelType)
    {
        $fuelType->forceDelete();

        return redirect()->route('fuel-types.trash')->with('success', 'Jenis BBM berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new FuelTypeDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new FuelTypeTrashDataTable)->responseFor($request);
    }
}
