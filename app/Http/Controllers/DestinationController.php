<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        return view('pages.destination.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'destination' => ['required', 'string'],
            'desc' => ['nullable', 'string'],
        ]);

        $created = Destination::create($request->only(['destination', 'desc']));

        if (! $created) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan tujuan');
        }

        return redirect()->route('destination.index')->with('success', 'Tujuan berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $destination = Destination::findOrFail($id);

        $request->validate([
            'destination' => ['required', 'string'],
            'desc' => ['nullable', 'string'],
        ]);

        $updated = $destination->update($request->only(['destination', 'desc']));

        if (! $updated) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui tujuan');
        }

        return redirect()->route('destination.index')->with('success', 'Tujuan berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $destination = Destination::findOrFail($id);

        if (! $destination->delete()) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus tujuan');
        }

        return redirect()->route('destination.index')->with('success', 'Tujuan berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $destinations = Destination::select(['id', 'destination', 'desc']);

        return datatables()->of($destinations)
            ->addIndexColumn()
            ->addColumn('action', function ($destination) {
                $canEdit = auth()->user()->hasPermission('master.destinations.edit');
                $canDelete = auth()->user()->hasPermission('master.destinations.delete');

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-destination"
                            href="#"
                            data-update-url="'.route('destination.update', $destination->id).'"
                            data-destination="'.e($destination->destination).'"
                            data-desc="'.e($destination->desc ?? '').'">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="'.route('destination.destroy', $destination->id).'" method="POST" style="display: inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus tujuan ini?\')">
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
