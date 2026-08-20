<?php

namespace App\Http\Controllers;

use App\DataTables\TypeMaintenanceDataTable;
use App\DataTables\TypeMaintenanceTrashDataTable;
use App\Http\Requests\TypeMaintenanceImportRequest;
use App\Http\Requests\TypeMaintenanceStoreRequest;
use App\Http\Requests\TypeMaintenanceUpdateRequest;
use App\Imports\TypeMaintenancesImport;
use App\Models\TypeMaintenances;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TypeMaintenanceController extends Controller
{
    public function index()
    {
        return view('type-maintenances.index', ['table' => new TypeMaintenanceDataTable]);
    }

    public function store(TypeMaintenanceStoreRequest $request)
    {
        TypeMaintenances::create($request->validated());

        return redirect()->route('type-maintenances.index')->with('success', 'Tipe maintenance berhasil ditambahkan.');
    }

    public function show(TypeMaintenances $typeMaintenance)
    {
        return view('type-maintenances.partials.show', compact('typeMaintenance'));
    }

    public function update(TypeMaintenances $typeMaintenance, TypeMaintenanceUpdateRequest $request)
    {
        $typeMaintenance->update($request->validated());

        return redirect()->route('type-maintenances.index')->with('success', 'Tipe maintenance berhasil diperbarui.');
    }

    public function destroy(TypeMaintenances $typeMaintenance)
    {
        $typeMaintenance->delete();

        return redirect()->route('type-maintenances.index')->with('success', 'Tipe maintenance berhasil disimpan di sampah.');
    }

    public function import(TypeMaintenanceImportRequest $request)
    {
        Excel::import(new TypeMaintenancesImport, $request->file('excelFile'));

        return redirect()->route('type-maintenances.index')->with('success', 'Tipe maintenance berhasil diimport.');
    }

    public function trash()
    {
        return view('type-maintenances.trash', ['table' => new TypeMaintenanceTrashDataTable]);
    }

    public function restore(TypeMaintenances $typeMaintenance)
    {
        $typeMaintenance->restore();

        return redirect()->route('type-maintenances.trash')->with('success', 'Tipe maintenance berhasil dipulihkan.');
    }

    public function forceDelete(TypeMaintenances $typeMaintenance)
    {
        $typeMaintenance->forceDelete();

        return redirect()->route('type-maintenances.trash')->with('success', 'Tipe maintenance berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new TypeMaintenanceDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new TypeMaintenanceTrashDataTable)->responseFor($request);
    }
}
