<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TypeMaintenances;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TypeMaintenancesImport;

class TypeMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.type-maintenance.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique('type_maintenances', 'name')],
        ]);

        TypeMaintenances::create([
            'name' => Str::upper($request->name),
        ]);

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $typeMaintenance = TypeMaintenances::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', Rule::unique('type_maintenances', 'name')->ignore($typeMaintenance->id)],
        ]);

        $typeMaintenance->update([
            'name' => Str::upper($request->name),
        ]);

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $typeMaintenance = TypeMaintenances::findOrFail($id);
        $typeMaintenance->delete();

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $types = TypeMaintenances::select(['id', 'name']);

        return DataTables::of($types)
            ->addIndexColumn()
            ->addColumn('action', function ($type) {
                $canEdit = auth()->user()->hasPermission('master.type_maintenances.edit');
                $canDelete = auth()->user()->hasPermission('master.type_maintenances.delete');

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-type-maintenance"
                            href="#"
                            data-update-url="' . route('type-maintenance.update', $type->id) . '"
                            data-name="' . e($type->name) . '">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="' . route('type-maintenance.destroy', $type->id) . '" method="POST" style="display: inline;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus tipe maintenance ini?\')">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </button>
                        </form>';
                }
                $actions .= '</div></div>';
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeResponse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        TypeMaintenances::create([
            'name' => Str::upper($request->name),
        ]);

        return response()->json([
            'data' => Str::upper($request->name)
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv',
        ]);
        Excel::import(new TypeMaintenancesImport(), $request->file('excelFile'));

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil diimport.');
    }

    public function type_maintenances_trash(Request $request)
    {
        if ($request->ajax()) {
            $data = TypeMaintenances::onlyTrashed()->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->can('activate data nonaktif')){
                        $btn .= '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#restoreDateModal" data-id="' . $row->id . '">Aktifkan</button> ';
                    }
                    if (auth()->user()->can('delete data nonaktif')){
                        $btn .= '<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="' . $row->id . '">Hapus</button>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $datas = [
            'pageTitle' => 'Data jenis perbaikan nonaktif',
        ];
        return view('pages.type-maintenance.trash', $datas);
    }

    public function restore(Request $request, $id)
    {
        $typeMaintenance = TypeMaintenances::withTrashed()->findOrFail($id);
        $typeMaintenance->restore();
        $typeMaintenance->save();

        return redirect()->route('type-maintenance.trash')->with('success', 'Jenis perbaikan berhasil diaktifkan kembali.');
    }

    public function forceDeleteConfirmed($id)
    {
        $typeMaintenance = TypeMaintenances::withTrashed()->findOrFail($id);
        $typeMaintenance->forceDelete();

        return redirect()->route('type-maintenance.trash')->with('success', 'Jenis perbaikan berhasil dihapus permanent.');
    }
}
