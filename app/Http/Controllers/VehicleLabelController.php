<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\View\View;

class VehicleLabelController extends Controller
{
    public function __invoke(Vehicle $vehicle): View
    {
        $vehicle->loadMissing(['company:id,name', 'vehicleType:id,name', 'vehicleBrand:id,name']);

        return view('vehicles.label', [
            'vehicle' => $vehicle,
            // QR points to the scan page with the plate prefilled, so scanning the
            // printed sticker opens the in-app scan/quick-action flow.
            'scanUrl' => route('qr-scan.index', ['barcode' => $vehicle->plat_new]),
        ]);
    }
}
