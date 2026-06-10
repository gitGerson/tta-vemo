<?php

namespace App\Http\Controllers;

use App\DataTables\VehiclePhotoDataTable;
use App\Http\Requests\VehiclePhotoStoreRequest;
use App\Http\Requests\VehiclePhotoUpdateRequest;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\FilePondUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiclePhotoController extends Controller
{
    public function __construct(protected FilePondUploadService $filePond) {}

    public function create(Request $request)
    {
        $vehicleId = $request->integer('vehicle_id') ?: null;
        $vehicle = $vehicleId !== null ? Vehicle::query()->find($vehicleId) : null;
        $periodsByVehicle = $this->documentedPeriodsByVehicle();

        return view('vehicle-photos.create', [
            'vehicle' => $vehicle,
            'vehicles' => Vehicle::query()->orderBy('plat_new')->get(['id', 'plat_new', 'type']),
            'selectedVehicleId' => $vehicle?->id,
            'periodsByVehicle' => $periodsByVehicle,
            'documentedPeriods' => $vehicle !== null ? ($periodsByVehicle[$vehicle->id] ?? []) : [],
        ]);
    }

    public function store(VehiclePhotoStoreRequest $request)
    {
        $data = $request->validated();
        $data['photos'] = $this->resolveImages($request, null, 'photos');

        $photo = VehiclePhoto::create($data);

        return $this->redirectAfterWrite($photo->vehicle_id, 'Foto kendaraan berhasil disimpan.');
    }

    public function edit(VehiclePhoto $vehiclePhoto)
    {
        return view('vehicle-photos.edit', [
            'photo' => $vehiclePhoto->load('vehicle'),
            'vehicle' => $vehiclePhoto->vehicle,
            'documentedPeriods' => $this->documentedPeriodsByVehicle()[$vehiclePhoto->vehicle_id] ?? [],
        ]);
    }

    public function update(VehiclePhoto $vehiclePhoto, VehiclePhotoUpdateRequest $request)
    {
        $data = $request->validated();
        $data['photos'] = $this->resolveImages($request, $vehiclePhoto, 'photos');

        $vehiclePhoto->update($data);

        return $this->redirectAfterWrite($vehiclePhoto->vehicle_id, 'Foto kendaraan berhasil diperbarui.');
    }

    public function destroy(VehiclePhoto $vehiclePhoto)
    {
        $vehicleId = $vehiclePhoto->vehicle_id;
        $vehiclePhoto->delete();

        return $this->redirectAfterWrite($vehicleId, 'Foto kendaraan berhasil dihapus.');
    }

    /**
     * Land on the vehicle detail page when the user can view it, otherwise fall back to
     * the standalone upload page (a photo-only user may lack master.vehicles.view).
     */
    private function redirectAfterWrite(int $vehicleId, string $message): RedirectResponse
    {
        $target = auth()->user()?->hasPermission('master.vehicles.view') === true
            ? redirect()->route('vehicles.show', $vehicleId)
            : redirect()->route('vehicle-photos.create');

        return $target->with('success', $message);
    }

    public function getDataForVehicle(Vehicle $vehicle, Request $request)
    {
        return (new VehiclePhotoDataTable($vehicle))->responseFor($request);
    }

    /**
     * Map each vehicle id to the list of months (Y-m) that already have a photo, so the
     * month picker can flag which months are documented vs still missing. Company-scoped
     * via the VehiclePhoto CompanyTenantScope.
     *
     * @return array<int, array<int, string>>
     */
    private function documentedPeriodsByVehicle(): array
    {
        return VehiclePhoto::query()
            ->get(['vehicle_id', 'period'])
            ->groupBy('vehicle_id')
            ->map(fn ($rows) => $rows->map(fn (VehiclePhoto $photo): string => $photo->period->format('Y-m'))->unique()->values()->all())
            ->all();
    }

    /**
     * Merge kept existing photos (re-submitted as their URL) with newly uploaded ones,
     * returning the resulting list of stored S3 paths.
     *
     * @return array<int, string>
     */
    private function resolveImages(Request $request, ?VehiclePhoto $photo, string $field): array
    {
        $existingPaths = $photo?->{$field} ?? [];

        $urlToPath = collect($existingPaths)->mapWithKeys(fn (string $path): array => [
            (str_starts_with($path, 'http') ? $path : Storage::disk('s3')->url($path)) => $path,
        ]);

        $kept = collect((array) $request->input($field, []))
            ->filter(fn ($value): bool => is_string($value) && $urlToPath->has($value))
            ->map(fn (string $url): string => $urlToPath->get($url))
            ->values()
            ->all();

        $new = $this->filePond->storeManyFromRequestField($request, $field, 'vehicle_photos', 's3');

        return array_values(array_merge($kept, $new));
    }
}
