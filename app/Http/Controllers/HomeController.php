<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Maintenances;
use App\Models\NotificationService;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [
            'driver' => Driver::get(),
            'vehicle' => Vehicle::where('status', 'ACTIVE')->get(),
            'pageTitle' => 'Beranda',
            'service' => NotificationService::where('read', 0)->get(),
            'mtc' => Maintenances::where('status', 'DRAFT')->get(),
        ];

        return view('home', $data);
    }

    public function markServiceNotificationRead(NotificationService $notification): RedirectResponse
    {
        if (! $notification->read) {
            $notification->update(['read' => 1]);
        }

        return redirect()->route('maintenances.create', ['vehicle' => $notification->no_pol]);
    }
}
