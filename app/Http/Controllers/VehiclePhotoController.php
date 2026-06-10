<?php

namespace App\Http\Controllers;

use App\DataTables\VehiclePhotoDataTable;
use App\Http\Requests\VehiclePhotoStoreRequest;
use App\Http\Requests\VehiclePhotoUpdateRequest;
use App\Models\Comment;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\FilePondUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

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
            'table' => new VehiclePhotoDataTable,
        ]);
    }

    public function store(VehiclePhotoStoreRequest $request)
    {
        $data = $request->validated();
        $data['photos'] = $this->resolveSidePhotos($request, null);

        $photo = VehiclePhoto::create($data);

        return redirect()
            ->route('vehicle-photos.create', ['vehicle_id' => $photo->vehicle_id])
            ->with('success', 'Foto kendaraan berhasil disimpan.');
    }

    public function show(VehiclePhoto $vehiclePhoto)
    {
        return view('vehicle-photos.show', [
            'photo' => $vehiclePhoto->load('vehicle', 'comments.user'),
            'vehicle' => $vehiclePhoto->vehicle,
        ]);
    }

    public function storeComment(VehiclePhoto $vehiclePhoto, Request $request)
    {
        $data = $request->validate(Comment::validationRules());

        // The rich editor posts HTML — sanitize it before persisting (rendered raw on show).
        $body = Purify::clean($data['body']);

        // An "empty" editor still posts markup like <p></p>; reject when there is no
        // visible text and no image.
        if (trim(strip_tags($body)) === '' && ! str_contains($body, '<img')) {
            return back()->withErrors(['body' => 'Komentar tidak boleh kosong.'])->withInput();
        }

        $vehiclePhoto->comments()->create([
            'body' => $body,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Komentar ditambahkan.');
    }

    public function destroyComment(VehiclePhoto $vehiclePhoto, Comment $comment)
    {
        abort_unless(
            $comment->commentable_id === $vehiclePhoto->id
                && $comment->commentable_type === $vehiclePhoto->getMorphClass()
                && $comment->user_id === auth()->id(),
            403
        );

        $comment->delete();

        return back()->with('success', 'Komentar dihapus.');
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
        $data['photos'] = $this->resolveSidePhotos($request, $vehiclePhoto);

        $vehiclePhoto->update($data);

        return redirect()
            ->route('vehicle-photos.create', ['vehicle_id' => $vehiclePhoto->vehicle_id])
            ->with('success', 'Foto kendaraan berhasil diperbarui.');
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
     * Global photo table data, optionally narrowed to one vehicle via the
     * `vehicle_id` ajax param (used by the upload page's filterable table).
     */
    public function getData(Request $request)
    {
        $vehicleId = $request->integer('vehicle_id') ?: null;
        $vehicle = $vehicleId !== null ? Vehicle::query()->find($vehicleId) : null;

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
     * Resolve the four side images into a `side => stored S3 path` map. Per side, a newly
     * uploaded file/temp takes precedence; otherwise the existing image is kept when it was
     * re-submitted as its current S3 URL; otherwise the side is left empty (removed).
     *
     * @return array<string, string>
     */
    private function resolveSidePhotos(Request $request, ?VehiclePhoto $photo): array
    {
        $result = [];

        foreach (array_keys(VehiclePhoto::SIDES) as $side) {
            $field = "photos.$side";

            $new = $this->filePond->storeFromRequestField($request, $field, 'vehicle_photos', 's3');
            if ($new !== null) {
                $result[$side] = $new;

                continue;
            }

            $existingPath = $photo?->photos[$side] ?? null;
            $existingUrl = $existingPath !== null ? $photo->sideUrl($side) : null;
            $submitted = $request->input($field);

            if (is_string($submitted) && $existingUrl !== null && $submitted === $existingUrl) {
                $result[$side] = $existingPath;
            }
        }

        return $result;
    }
}
