<?php

namespace App\Http\Controllers;

use App\DataTables\DestinationDataTable;
use App\DataTables\DestinationTrashDataTable;
use App\Http\Requests\DestinationStoreRequest;
use App\Http\Requests\DestinationUpdateRequest;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        return view('destinations.index', ['table' => new DestinationDataTable]);
    }

    public function store(DestinationStoreRequest $request)
    {
        return redirect()->route('destinations.index')->with('success', 'Tujuan memakai data kota Laravolt Indonesia untuk preview.');
    }

    public function show(Destination $destination)
    {
        $destination->loadMissing('province');

        return view('destinations.partials.show', compact('destination'));
    }

    public function update(Destination $destination, DestinationUpdateRequest $request)
    {
        return redirect()->route('destinations.index')->with('success', 'Tujuan memakai data kota Laravolt Indonesia untuk preview.');
    }

    public function destroy(Destination $destination)
    {
        return redirect()->route('destinations.index')->with('success', 'Data kota Laravolt Indonesia tidak dihapus dari modul tujuan preview.');
    }

    public function trash()
    {
        return view('destinations.trash', ['table' => new DestinationTrashDataTable]);
    }

    public function restore(Destination $destination)
    {
        return redirect()->route('destinations.index')->with('success', 'Tujuan memakai data kota Laravolt Indonesia untuk preview.');
    }

    public function forceDelete(Destination $destination)
    {
        return redirect()->route('destinations.index')->with('success', 'Data kota Laravolt Indonesia tidak dihapus dari modul tujuan preview.');
    }

    public function getData(Request $request)
    {
        return (new DestinationDataTable)->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new DestinationTrashDataTable)->responseFor($request);
    }
}
