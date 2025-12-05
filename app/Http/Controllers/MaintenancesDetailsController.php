<?php

namespace App\Http\Controllers;

use App\Models\TypeMaintenances;
use Illuminate\Support\Str;
use App\Models\Maintenances;
use Illuminate\Http\Request;
use App\Models\MaintenancesDetails;

class MaintenancesDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'maintenance_id' => 'required',
            'maintenance_name' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'material' => 'nullable',
        ]);

        MaintenancesDetails::create([
            'maintenance_id' => $request->maintenance_id,
            'maintenance_name' => Str::upper($request->maintenance_name),
            'price' => $request->price,
            'qty' => $request->qty,
            'material' => $request->material,
        ]);

        return redirect()->back()->with('success', 'Detail perbaikan berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return response()->json(
            [
                'success' => true,
                'data' => MaintenancesDetails::find($id),
                'jenisMtc' => TypeMaintenances::get(),
            ],
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'maintenance_name' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'material' => 'nullable',
        ]);

        MaintenancesDetails::find($id)->update([
            'maintenance_name' => Str::upper($request->maintenance_name),
            'price' => $request->price,
            'qty' => $request->qty,
            'material' => $request->material,
        ]);

        return redirect()->back()->with('success', 'Detail perbaikan berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $detail = MaintenancesDetails::find($id);
        $detail->delete();
        return redirect()->back()->with('success', 'Detail perbaikan berhasil dihapus');
    }
}
