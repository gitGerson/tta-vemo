<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverStoreRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Models\Driver;
use App\Services\FilePondUploadService;
use App\Services\JPayrollService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
    public function __construct(
        protected JPayrollService $jpayroll,
        protected FilePondUploadService $filePond,
    ) {}

    public function index()
    {
        return view('drivers.index');
    }

    public function store(DriverStoreRequest $request)
    {
        $data = $request->validated();
        $nik = $data['nik'] ?? null;

        if ($nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3');

        Driver::create($data);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function show(Driver $driver)
    {
        return view('drivers.show', compact('driver'));
    }

    public function update(Driver $driver, DriverUpdateRequest $request)
    {
        $data = $request->validated();
        $nik = $data['nik'] ?? null;

        if ($nik && $nik !== $driver->nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3') ?? $driver->image;

        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroy(Driver $driver, Request $request)
    {
        $driver->out_date = $request->input('out_date') ?? $driver->out_date;
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

    public function getData()
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
            ->addColumn('row_url', fn ($driver) => route('drivers.show', $driver))
            ->addColumn('action', function ($driver) {
                $canEdit = auth()->user()->hasPermission('master.drivers.edit');
                $canDelete = auth()->user()->hasPermission('master.drivers.delete');

                return view('datatable.actions.drivers', compact('driver', 'canEdit', 'canDelete'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getTrashData()
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
            ->addColumn('action', fn ($driver) => view('datatable.actions.drivers-trash', compact('driver'))->render())
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getEmpInfo(Request $request)
    {
        $request->validate(['nik' => 'required|string']);

        $nik = $request->input('nik');
        $result = $this->jpayroll->fetchEmployeeByNik($nik);

        return response()->json($result);
    }
}
