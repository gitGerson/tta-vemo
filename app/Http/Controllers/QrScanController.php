<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrScanController extends Controller
{
    public function index(Request $request): View
    {
        return view('qr-scan.index', [
            'initialBarcode' => $request->string('barcode')->trim()->value(),
        ]);
    }

    public function resolve(Request $request): JsonResponse
    {
        $barcode = $this->normalizeBarcode($request->string('barcode')->trim()->value());

        if (blank($barcode)) {
            return response()->json(['error' => 'Input tidak boleh kosong.'], 422);
        }

        // Company-scoped automatically via the Vehicle BelongsToCompanyTenant global scope.
        $vehicle = Vehicle::where('plat_new', $barcode)
            ->with(['company', 'vehicleType', 'vehicleBrand', 'fuelType'])
            ->first();

        if (! $vehicle) {
            return response()->json(['error' => "Kendaraan tidak ditemukan untuk plat: {$barcode}"], 404);
        }

        return response()->json([
            'item' => $this->buildItemData($vehicle),
            'actions' => $this->buildActions($vehicle),
        ]);
    }

    /** @return array<string, mixed> */
    private function buildItemData(Vehicle $vehicle): array
    {
        $typeLabel = $vehicle->vehicleType?->name ?? $vehicle->type;
        $brandName = $vehicle->vehicleBrand?->name;

        return [
            'id' => $vehicle->id,
            'identifier' => $vehicle->plat_new,
            'name' => trim(($typeLabel ?: '').($brandName ? ' / '.$brandName : '')) ?: null,
            'image_url' => $vehicle->vehicleBrand?->logoUrl(),
            'company' => $vehicle->company?->name,
            'type' => $typeLabel,
            'entry_year' => $vehicle->entry_year,
            'mileage' => $vehicle->mileage,
            'fuel' => $vehicle->fuelType?->name ?? $vehicle->fuel_type,
            'stnk_due_date' => $vehicle->stnk_due_date?->format('d M Y'),
            'kir_due_date' => $vehicle->kir_due_date?->format('d M Y'),
            'status' => [
                'name' => $vehicle->status,
                'color' => $vehicle->status === 'ACTIVE' ? '#71dd37' : '#8592a3',
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function buildActions(Vehicle $vehicle): array
    {
        $user = auth()->user();

        return [
            'view' => [
                'url' => route('vehicles.show', $vehicle),
                'allowed' => $user->hasPermission('master.vehicles.view'),
            ],
            'label' => [
                'url' => route('vehicles.label', $vehicle),
                'allowed' => $user->hasPermission('master.vehicles.view'),
            ],
            'maintenance' => [
                'url' => route('maintenances.create', ['vehicle' => $vehicle->plat_new]),
                'allowed' => $user->hasPermission('operational.maintenances.create'),
            ],
            'photo' => [
                'url' => route('vehicle-photos.create', ['vehicle_id' => $vehicle->id]),
                'allowed' => $user->hasPermission('operational.vehicle_photos.create'),
            ],
        ];
    }

    /**
     * Accept either a raw plate or a scanned QR URL that carries the plate in its
     * `barcode` query string (e.g. the printed label QR points to the scan page).
     */
    private function normalizeBarcode(string $barcode): string
    {
        if (filter_var($barcode, FILTER_VALIDATE_URL) === false) {
            return $barcode;
        }

        $query = parse_url($barcode, PHP_URL_QUERY);

        if (! is_string($query)) {
            return $barcode;
        }

        parse_str($query, $params);

        return is_string($params['barcode'] ?? null)
            ? trim($params['barcode'])
            : $barcode;
    }
}
