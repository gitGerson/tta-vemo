<?php

namespace App\Http\Controllers;

use App\DataTables\VehicleDataTable;
use App\DataTables\VehicleSnapshotDataTable;
use App\DataTables\VehicleTrashDataTable;
use App\Http\Requests\VehicleStoreRequest;
use App\Http\Requests\VehicleUpdateRequest;
use App\Models\Company;
use App\Models\Vehicle;
use App\Services\FilePondUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function __construct(protected FilePondUploadService $filePond) {}

    public function index()
    {
        return view('vehicles.index', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'table' => new VehicleDataTable,
        ]);
    }

    public function store(VehicleStoreRequest $request)
    {
        $data = $request->validated();
        $data['bpkb_image'] = $this->resolveImages($request, null, 'bpkb_image');
        $data['stnk_image'] = $this->resolveImages($request, null, 'stnk_image');

        Vehicle::create($data);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('company');

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'snapshotTable' => new VehicleSnapshotDataTable($vehicle),
        ]);
    }

    public function update(Vehicle $vehicle, VehicleUpdateRequest $request)
    {
        $data = $request->validated();
        $data['bpkb_image'] = $this->resolveImages($request, $vehicle, 'bpkb_image');
        $data['stnk_image'] = $this->resolveImages($request, $vehicle, 'stnk_image');

        $vehicle->update($data);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('vehicles.trash', ['table' => new VehicleTrashDataTable]);
    }

    public function restore(Vehicle $vehicle)
    {
        $vehicle->restore();

        return redirect()->route('vehicles.trash')->with('success', 'Kendaraan berhasil dipulihkan.');
    }

    public function forceDelete(Vehicle $vehicle)
    {
        $vehicle->forceDelete();

        return redirect()->route('vehicles.trash')->with('success', 'Kendaraan berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        return (new VehicleDataTable)->responseFor($request);
    }

    public function getSnapshotData(Vehicle $vehicle, Request $request)
    {
        return (new VehicleSnapshotDataTable($vehicle))->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new VehicleTrashDataTable)->responseFor($request);
    }

    /**
     * Merge kept existing document images (re-submitted as their URL) with newly
     * uploaded ones, returning the resulting list of stored S3 paths.
     *
     * @return array<int, string>
     */
    private function resolveImages(Request $request, ?Vehicle $vehicle, string $field): array
    {
        $existingPaths = $vehicle?->{$field} ?? [];

        $urlToPath = collect($existingPaths)->mapWithKeys(fn (string $path): array => [
            (str_starts_with($path, 'http') ? $path : Storage::disk('s3')->url($path)) => $path,
        ]);

        $kept = collect((array) $request->input($field, []))
            ->filter(fn ($value): bool => is_string($value) && $urlToPath->has($value))
            ->map(fn (string $url): string => $urlToPath->get($url))
            ->values()
            ->all();

        $new = $this->filePond->storeManyFromRequestField($request, $field, 'vehicle_documents', 's3');

        return array_values(array_merge($kept, $new));
    }
}
