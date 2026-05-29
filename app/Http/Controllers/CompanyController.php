<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.company.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', Rule::unique('company', 'code')],
            'name' => ['required', 'string'],
            'desc' => ['nullable', 'string'],
        ]);

        $created = Company::create([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'desc' => $request->input('desc'),
        ]);

        if (! $created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan perusahaan');
        }

        return redirect()->route('company.index')->with('success', 'Perusahaan Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            'code' => ['required', 'string', Rule::unique('company', 'code')->ignore($company->id)],
            'name' => ['required', 'string'],
            'desc' => ['nullable', 'string'],
        ]);

        $updated = $company->update([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'desc' => $request->input('desc'),
        ]);

        if (! $updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui perusahaan');
        }

        return redirect()->route('company.index')->with('success', 'Perusahaan Berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);

        if (! $company->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus perusahaan');
        }

        return redirect()->route('company.index')->with('success', 'Perusahaan Berhasil Dihapus');
    }

    public function getData(Request $request)
    {
        $companies = Company::select(['id', 'name', 'code', 'desc']);

        return datatables()->of($companies)
            ->addIndexColumn()
            ->addColumn('action', function ($company) {
                $canEdit = auth()->user()->hasPermission('master.companies.edit');
                $canDelete = auth()->user()->hasPermission('master.companies.delete');

                $name = e($company->name);
                $code = e($company->code);
                $desc = e($company->desc ?? '');

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-company"
                            href="#"
                            data-update-url="'.route('company.update', $company->id).'"
                            data-code="'.$code.'"
                            data-name="'.$name.'"
                            data-desc="'.$desc.'">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="'.route('company.destroy', $company->id).'" method="POST" style="display: inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus perusahaan ini?\')">
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
