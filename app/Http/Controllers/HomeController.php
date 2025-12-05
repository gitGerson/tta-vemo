<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\NotificationService;
use App\Models\Maintenances;

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
            'mtc' => Maintenances::where('status', 'PENDING')->get()
        ];
        return view('home' , $data);
    }
}
