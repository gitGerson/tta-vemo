<?php

namespace App\Http\Controllers;

use App\DataTables\CompanyDataTable;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;

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

    public function show(Company $company)
    {
        return view('companies.partials.show', compact('company'));
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

    public function getData()
    {
        return (new CompanyDataTable)->response();
    }
}
