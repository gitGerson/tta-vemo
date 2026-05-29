<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverStoreRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Models\Driver;
use App\Services\JPayrollService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
    public function __construct(protected JPayrollService $jpayroll) {}

    public function index(Request $request)
    {
        return view('drivers.index');
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(DriverStoreRequest $request)
    {
        $data = $request->validated();
        $nik = $data['nik'] ?? null;

        if ($nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->handleImageUpload($request);

        Driver::create($data);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function show(Driver $driver)
    {
        return view('drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Driver $driver, DriverUpdateRequest $request)
    {
        $data = $request->validated();
        $nik = $data['nik'] ?? null;

        if ($nik && $nik !== $driver->nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->handleImageUpload($request, $driver->image);

        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroy(Driver $driver, Request $request)
    {
        $driver->out_date = $request->out_date ?? $driver->out_date;
        $driver->status = 'PASSIVE';
        $driver->saveQuietly();
        $driver->delete();

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('drivers.trash');
    }

    public function restore(Driver $driver)
    {
        $driver->restore();

        return redirect()->route('drivers.trash')->with('success', 'Driver berhasil dipulihkan.');
    }

    public function forceDelete(Driver $driver)
    {
        $driver->forceDelete();

        return redirect()->route('drivers.trash')->with('success', 'Driver berhasil dihapus permanen.');
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

        return DataTables::of($drivers)
            ->addIndexColumn()
            ->addColumn('row_url', function ($driver) {
                return route('drivers.show', $driver);
            })
            ->addColumn('action', function ($driver) {
                $canEdit = auth()->user()->hasPermission('master.drivers.edit');
                $canDelete = auth()->user()->hasPermission('master.drivers.delete');

                return view('datatable.actions.drivers', compact('driver', 'canEdit', 'canDelete'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getTrashData(Request $request)
    {
        $drivers = Driver::onlyTrashed()->select([
            'id',
            'name',
            'no_identity',
            'no_phone_1',
            'status',
            'deleted_at',
        ]);

        return DataTables::of($drivers)
            ->addIndexColumn()
            ->addColumn('action', function ($driver) {
                return view('datatable.actions.drivers-trash', compact('driver'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getEmpInfo(Request $request)
    {
        $request->validate(['nik' => 'required|string']);

        $nik = $request->nik;
        $result = $this->jpayroll->fetchEmployeeByNik($nik);

        return response()->json($result);
    }

    private function handleImageUpload(Request $request, ?string $currentImage = null): ?string
    {
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('profile_images'), $imageName);

            return $imageName;
        }

        if ($request->filled('image')) {
            return $request->image;
        }

        return $currentImage;
    }
}
