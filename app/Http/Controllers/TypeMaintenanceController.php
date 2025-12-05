<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\TypeMaintenances;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TypeMaintenanceController extends Controller
{
    public function index()
    {
        return view('pages.type-maintenance.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique('type_maintenances', 'name')],
        ]);

        $created = TypeMaintenances::create($request->only(['name']));

        if (!$created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan tipe maintenance');
        }

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $typeMaintenance = TypeMaintenances::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', Rule::unique('type_maintenances', 'name')->ignore($typeMaintenance->id)],
        ]);

        $updated = $typeMaintenance->update($request->only(['name']));

        if (!$updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui tipe maintenance');
        }

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $typeMaintenance = TypeMaintenances::findOrFail($id);

        if (!$typeMaintenance->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus tipe maintenance');
        }

        return redirect()->route('type-maintenance.index')->with('success', 'Tipe maintenance berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $types = TypeMaintenances::select(['id', 'name']);

        return datatables()->of($types)
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
}
