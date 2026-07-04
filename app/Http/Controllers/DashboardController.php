<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's learning dashboard.
     */
    public function index(Request $request): View
    {
        return view('dashboard');
    }
}
