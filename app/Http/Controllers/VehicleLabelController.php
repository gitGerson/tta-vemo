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
            'vehicleUrl' => route('vehicles.show', $vehicle),
        ]);
    }
}
