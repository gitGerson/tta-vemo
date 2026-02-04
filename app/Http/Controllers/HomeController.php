<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Maintenances;
use App\Models\NotificationService;
use App\Models\Vehicle;

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
            'mtc' => Maintenances::where('status', 'IN_PROGRESS')->get(),
        ];

        return view('home', $data);
    }
}
