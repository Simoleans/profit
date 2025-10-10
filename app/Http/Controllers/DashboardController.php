<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     * Los datos se cargarán de forma asíncrona desde el frontend.
     */
    public function index()
    {
        return Inertia::render('Dashboard');
    }
}
