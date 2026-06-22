<?php

namespace App\Http\Controllers;

use App\DataTables\CompanyDataTable;
use App\DataTables\CompanyDriverDataTable;
use App\DataTables\CompanyTrashDataTable;
use App\DataTables\CompanyUserDataTable;
use App\DataTables\CompanyVehicleDataTable;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index', ['table' => new CompanyDataTable]);
    }

    public function store(CompanyStoreRequest $request)
    {
        Company::create($request->validated());

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function show(Company $company): View
    {
        $company->loadCount(['drivers', 'users', 'vehicles']);

        return view('companies.show', [
            'company' => $company,
            'driverTable' => new CompanyDriverDataTable($company),
            'userTable' => new CompanyUserDataTable($company),
            'vehicleTable' => new CompanyVehicleDataTable($company),
        ]);
    }

    public function update(Company $company, CompanyUpdateRequest $request)
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil diperbarui.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil dihapus.');
    }

    public function trash()
    {
        return view('companies.trash', ['table' => new CompanyTrashDataTable]);
    }

    public function restore(Company $company)
    {
        $company->restore();

        return redirect()->route('companies.trash')->with('success', 'Perusahaan berhasil dipulihkan.');
    }

    public function forceDelete(Company $company)
    {
        $company->forceDelete();

        return redirect()->route('companies.trash')->with('success', 'Perusahaan berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new CompanyDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request): JsonResponse
    {
        return (new CompanyTrashDataTable)->responseFor($request);
    }

    public function getDriverData(Request $request, Company $company): JsonResponse
    {
        return (new CompanyDriverDataTable($company))->responseFor($request);
    }

    public function getUserData(Request $request, Company $company): JsonResponse
    {
        return (new CompanyUserDataTable($company))->responseFor($request);
    }

    public function getVehicleData(Request $request, Company $company): JsonResponse
    {
        return (new CompanyVehicleDataTable($company))->responseFor($request);
    }
}
