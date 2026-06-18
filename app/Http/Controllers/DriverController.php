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
use App\Services\PayrollCompanyResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function __construct(
        protected JPayrollService $jpayroll,
        protected FilePondUploadService $filePond,
        protected PayrollCompanyResolver $payrollCompany,
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
        $sourceCompanyCode = Arr::pull($data, 'source_company_code');
        $sourceCompanyName = Arr::pull($data, 'source_company_name');
        $nik = $data['nik'] ?? null;

        if ($nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3');

        DB::transaction(function () use ($companyIds, $data, $sourceCompanyCode, $sourceCompanyName): void {
            $driver = Driver::create($data);
            $resolvedCompanyIds = $this->payrollCompany->mergeResolvedCompanyIds($companyIds, $sourceCompanyCode, $sourceCompanyName);

            $driver->companies()->sync($resolvedCompanyIds);
        });

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
        $sourceCompanyCode = Arr::pull($data, 'source_company_code');
        $sourceCompanyName = Arr::pull($data, 'source_company_name');
        $nik = $data['nik'] ?? null;

        if ($nik && $nik !== $driver->nik && ! $this->jpayroll->validateNik($nik)) {
            return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
        }

        $data['image'] = $this->filePond->storeFromRequestField($request, 'image', 'profile_images', 's3') ?? $driver->image;

        DB::transaction(function () use ($companyIds, $data, $driver, $sourceCompanyCode, $sourceCompanyName): void {
            $driver->update($data);
            $resolvedCompanyIds = $this->payrollCompany->mergeResolvedCompanyIds($companyIds, $sourceCompanyCode, $sourceCompanyName);

            $driver->companies()->sync($resolvedCompanyIds);
        });

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

    public function getData(Request $request)
    {
        return (new DriverDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new DriverTrashDataTable)->responseFor($request);
    }

    public function getEmpInfo(Request $request)
    {
        $request->validate(['nik' => 'required|string']);

        $nik = $request->input('nik');
        $result = $this->jpayroll->fetchEmployeeByNik($nik);

        if (($result['success'] ?? false) && isset($result['data']) && is_array($result['data'])) {
            $result['data'] = array_map(function (mixed $employee): mixed {
                if (is_object($employee)) {
                    $employee = (array) $employee;
                }

                if (is_array($employee)) {
                    $employee['_company'] = $this->payrollCompany->normalize($employee);
                }

                return $employee;
            }, $result['data']);
        }

        return response()->json($result);
    }
}
