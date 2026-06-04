<?php

namespace App\Http\Controllers;

use App\DataTables\FuelTypeDataTable;
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

        return redirect()->route('fuel-types.index')->with('success', 'Fuel type berhasil ditambahkan.');
    }

    public function show(FuelType $fuelType)
    {
        return view('fuel-types.partials.show', compact('fuelType'));
    }

    public function update(FuelType $fuelType, FuelTypeUpdateRequest $request)
    {
        $fuelType->update($request->validated());

        return redirect()->route('fuel-types.index')->with('success', 'Fuel type berhasil diperbarui.');
    }

    public function destroy(FuelType $fuelType)
    {
        $fuelType->delete();

        return redirect()->route('fuel-types.index')->with('success', 'Fuel type berhasil dihapus.');
    }

    public function getData(Request $request)
    {
        return (new FuelTypeDataTable)->responseFor($request);
    }
}
