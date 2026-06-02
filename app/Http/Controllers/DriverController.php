<?php

namespace App\Http\Controllers;

use App\DataTables\DriverDataTable;
use App\DataTables\DriverTrashDataTable;
use App\Http\Requests\DriverStoreRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Models\Company;
use App\Models\Driver;
use App\Services\FilePondUploadService;
use App\Services\JPayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DriverController extends Controller
{
    public function __construct(
        protected JPayrollService $jpayroll,
        protected FilePondUploadService $filePond,
    ) {}

    public function index()
    {
        return view('drivers.index', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'table' => new DriverDataTable,
        ]);
    }

    public function store(DriverStoreRequest $request)
    {
        $data = $request->validated();
        $companyIds = Arr::pull($data, 'company_ids', []);
        $nik = $data['nik'] ?? null;

        if ($nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3');

        $driver = Driver::create($data);
        $driver->companies()->sync($companyIds);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function show(Driver $driver)
    {
        $driver->load('companies');

        // Detail is rendered inside a modal on the index via AJAX; return the
        // partial fragment that the modal body loads.
        return view('drivers.partials.show', compact('driver'));
    }

    public function update(Driver $driver, DriverUpdateRequest $request)
    {
        $data = $request->validated();
        $companyIds = Arr::pull($data, 'company_ids', []);
        $nik = $data['nik'] ?? null;

        if ($nik && $nik !== $driver->nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3') ?? $driver->image;

        $driver->update($data);
        $driver->companies()->sync($companyIds);

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
        return view('drivers.trash', ['table' => new DriverTrashDataTable]);
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
        return (new DriverDataTable)->response();
    }

    public function getTrashData()
    {
        return (new DriverTrashDataTable)->response();
    }

    public function getEmpInfo(Request $request)
    {
        $request->validate(['nik' => 'required|string']);

        $nik = $request->input('nik');
        $result = $this->jpayroll->fetchEmployeeByNik($nik);

        return response()->json($result);
    }
}
