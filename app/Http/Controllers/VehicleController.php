<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        return view('pages.vehicle.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string'],
            'entry_year' => ['required', 'string'],
            'mileage' => ['nullable', 'string'],
            'plat_new' => ['required', 'string', Rule::unique('vehicles', 'plat_new')],
            'plat_old' => ['nullable', 'string', Rule::unique('vehicles', 'plat_old')],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            'mtc' => ['required', Rule::in(['YES', 'NO'])],
            'desc' => ['nullable', 'string'],
            'isprivate' => ['required', 'boolean'],
        ]);

        $created = Vehicle::create($request->only([
            'type',
            'entry_year',
            'mileage',
            'plat_new',
            'plat_old',
            'status',
            'mtc',
            'desc',
            'isprivate',
        ]));

        if (!$created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan kendaraan');
        }

        return redirect()->route('vehicle.index')->with('success', 'Kendaraan berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'type' => ['required', 'string'],
            'entry_year' => ['required', 'string'],
            'mileage' => ['nullable', 'string'],
            'plat_new' => ['required', 'string', Rule::unique('vehicles', 'plat_new')->ignore($vehicle->id)],
            'plat_old' => ['nullable', 'string', Rule::unique('vehicles', 'plat_old')->ignore($vehicle->id)],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            'mtc' => ['required', Rule::in(['YES', 'NO'])],
            'desc' => ['nullable', 'string'],
            'isprivate' => ['required', 'boolean'],
        ]);

        $updated = $vehicle->update($request->only([
            'type',
            'entry_year',
            'mileage',
            'plat_new',
            'plat_old',
            'status',
            'mtc',
            'desc',
            'isprivate',
        ]));

        if (!$updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui kendaraan');
        }

        return redirect()->route('vehicle.index')->with('success', 'Kendaraan berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        if (!$vehicle->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus kendaraan');
        }

        return redirect()->route('vehicle.index')->with('success', 'Kendaraan berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $vehicles = Vehicle::select([
            'id',
            'type',
            'entry_year',
            'mileage',
            'plat_new',
            'plat_old',
            'status',
            'mtc',
            'desc',
            'isprivate',
        ]);

        return datatables()->of($vehicles)
            ->addIndexColumn()
            ->addColumn('isprivate_label', function ($vehicle) {
                return $vehicle->isprivate ? 'Yes' : 'No';
            })
            ->addColumn('action', function ($vehicle) {
                $canEdit = auth()->user()->hasPermission('master.vehicles.edit');
                $canDelete = auth()->user()->hasPermission('master.vehicles.delete');

                $attrs = [
                    'type' => e($vehicle->type),
                    'entry_year' => e($vehicle->entry_year),
                    'mileage' => e($vehicle->mileage),
                    'plat_new' => e($vehicle->plat_new),
                    'plat_old' => e($vehicle->plat_old),
                    'status' => e($vehicle->status),
                    'mtc' => e($vehicle->mtc),
                    'desc' => e($vehicle->desc),
                    'isprivate' => $vehicle->isprivate,
                ];

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-vehicle"
                            href="#"
                            data-update-url="' . route('vehicle.update', $vehicle->id) . '"
                            data-type="' . $attrs['type'] . '"
                            data-entry_year="' . $attrs['entry_year'] . '"
                            data-mileage="' . $attrs['mileage'] . '"
                            data-plat_new="' . $attrs['plat_new'] . '"
                            data-plat_old="' . $attrs['plat_old'] . '"
                            data-status="' . $attrs['status'] . '"
                            data-mtc="' . $attrs['mtc'] . '"
                            data-desc="' . e($vehicle->desc ?? '') . '"
                            data-isprivate="' . ($attrs['isprivate'] ? 1 : 0) . '">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="' . route('vehicle.destroy', $vehicle->id) . '" method="POST" style="display: inline;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus kendaraan ini?\')">
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
}
