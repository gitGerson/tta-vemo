<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuelTypeStoreRequest;
use App\Http\Requests\FuelTypeUpdateRequest;
use App\Models\FuelType;

class FuelTypeController extends Controller
{
    public function index()
    {
        return view('pages.fuel-type.index');
    }

    public function store(FuelTypeStoreRequest $request)
    {
        $created = FuelType::create($request->validated());

        if (! $created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan jenis BBM');
        }

        return redirect()->route('fuel-type.index')->with('success', 'Jenis BBM berhasil ditambahkan');
    }

    public function update(FuelTypeUpdateRequest $request, FuelType $fuel_type)
    {
        $updated = $fuel_type->update($request->validated());

        if (! $updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui jenis BBM');
        }

        return redirect()->route('fuel-type.index')->with('success', 'Jenis BBM berhasil diperbarui');
    }

    public function destroy(FuelType $fuel_type)
    {
        if (! $fuel_type->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus jenis BBM');
        }

        return redirect()->route('fuel-type.index')->with('success', 'Jenis BBM berhasil dihapus');
    }

    public function getData()
    {
        $fuelTypes = FuelType::query()
            ->select(['id', 'name', 'price']);

        return datatables()->of($fuelTypes)
            ->addIndexColumn()
            ->editColumn('price', fn (FuelType $fuelType) => number_format((float) $fuelType->price, 2, ',', '.'))
            ->addColumn('action', function (FuelType $fuelType) {
                $canEdit = auth()->user()->hasPermission('master.fuel_types.edit');
                $canDelete = auth()->user()->hasPermission('master.fuel_types.delete');

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-fuel-type"
                            href="#"
                            data-update-url="'.route('fuel-type.update', $fuelType).'"
                            data-name="'.e($fuelType->name).'"
                            data-price="'.e(number_format((float) $fuelType->price, 2, '.', '')).'">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }

                if ($canDelete) {
                    $actions .= '<form action="'.route('fuel-type.destroy', $fuelType).'" method="POST" style="display: inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus jenis BBM ini?\')">
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
