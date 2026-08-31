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
        Destination::create($request->validated());

        return redirect()->route('destinations.index')->with('success', 'Tujuan berhasil ditambahkan.');
    }

    public function show(Destination $destination)
    {
        return view('destinations.partials.show', compact('destination'));
    }

    public function update(Destination $destination, DestinationUpdateRequest $request)
    {
        $destination->update($request->validated());

        return redirect()->route('destinations.index')->with('success', 'Tujuan berhasil diperbarui.');
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();

        return redirect()->route('destinations.index')->with('success', 'Tujuan berhasil dihapus.');
    }

    public function trash()
    {
        return view('destinations.trash', ['table' => new DestinationTrashDataTable]);
    }

    public function restore(Destination $destination)
    {
        $destination->restore();

        return redirect()->route('destinations.trash')->with('success', 'Tujuan berhasil dipulihkan.');
    }

    public function forceDelete(Destination $destination)
    {
        $destination->forceDelete();

        return redirect()->route('destinations.trash')->with('success', 'Tujuan berhasil dihapus permanen.');
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
