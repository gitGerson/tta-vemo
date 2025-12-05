<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index()
    {
        return view('pages.driver.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['nullable', 'string'],
            'nik' => ['nullable', 'string'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'no_identity' => ['required', 'string', Rule::unique('drivers', 'no_identity')],
            'residence' => ['required', 'string'],
            'date_birth' => ['required', 'date'],
            'no_phone_1' => ['required', 'string'],
            'no_phone_2' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
            'out_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
        ]);

        $created = Driver::create($request->only([
            'image',
            'nik',
            'name',
            'address',
            'no_identity',
            'residence',
            'date_birth',
            'no_phone_1',
            'no_phone_2',
            'entry_date',
            'out_date',
            'status',
        ]));

        if (!$created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan driver');
        }

        return redirect()->route('driver.index')->with('success', 'Driver berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'image' => ['nullable', 'string'],
            'nik' => ['nullable', 'string'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'no_identity' => ['required', 'string', Rule::unique('drivers', 'no_identity')->ignore($driver->id)],
            'residence' => ['required', 'string'],
            'date_birth' => ['required', 'date'],
            'no_phone_1' => ['required', 'string'],
            'no_phone_2' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
            'out_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
        ]);

        $updated = $driver->update($request->only([
            'image',
            'nik',
            'name',
            'address',
            'no_identity',
            'residence',
            'date_birth',
            'no_phone_1',
            'no_phone_2',
            'entry_date',
            'out_date',
            'status',
        ]));

        if (!$updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui driver');
        }

        return redirect()->route('driver.index')->with('success', 'Driver berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $driver = Driver::findOrFail($id);

        if (!$driver->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus driver');
        }

        return redirect()->route('driver.index')->with('success', 'Driver berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $drivers = Driver::select([
            'id',
            'name',
            'address',
            'no_identity',
            'residence',
            'date_birth',
            'no_phone_1',
            'no_phone_2',
            'entry_date',
            'out_date',
            'status',
            'image',
            'nik',
        ]);

        return datatables()->of($drivers)
            ->addIndexColumn()
            ->addColumn('action', function ($driver) {
                $canEdit = auth()->user()->hasPermission('master.drivers.edit');
                $canDelete = auth()->user()->hasPermission('master.drivers.delete');

                $attrs = [
                    'name' => e($driver->name),
                    'address' => e($driver->address),
                    'no_identity' => e($driver->no_identity),
                    'residence' => e($driver->residence),
                    'date_birth' => e($driver->date_birth),
                    'no_phone_1' => e($driver->no_phone_1),
                    'no_phone_2' => e($driver->no_phone_2),
                    'entry_date' => e($driver->entry_date),
                    'out_date' => e($driver->out_date),
                    'status' => e($driver->status),
                    'image' => e($driver->image),
                    'nik' => e($driver->nik),
                ];

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-driver"
                            href="#"
                            data-update-url="' . route('driver.update', $driver->id) . '"
                            data-name="' . $attrs['name'] . '"
                            data-address="' . $attrs['address'] . '"
                            data-no_identity="' . $attrs['no_identity'] . '"
                            data-residence="' . $attrs['residence'] . '"
                            data-date_birth="' . $attrs['date_birth'] . '"
                            data-no_phone_1="' . $attrs['no_phone_1'] . '"
                            data-no_phone_2="' . $attrs['no_phone_2'] . '"
                            data-entry_date="' . $attrs['entry_date'] . '"
                            data-out_date="' . $attrs['out_date'] . '"
                            data-status="' . $attrs['status'] . '"
                            data-image="' . $attrs['image'] . '"
                            data-nik="' . $attrs['nik'] . '">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="' . route('driver.destroy', $driver->id) . '" method="POST" style="display: inline;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus driver ini?\')">
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

    public function checkDriver(Request $request)
    {
        $data = Driver::where('id', '!=', $request->driver_1)->get();
        return response()->json(
            [
                'data' => $data,
            ],
            200,
        );
    }
}
