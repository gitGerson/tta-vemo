<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index()
    {
        // Logic for displaying mobile view
        return view('mobile.index');
    }
}
