<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index()
    {
        $user = Auth::user();

        // Obtener estadísticas de clientes según el rol del usuario
        if ($user->rol == 0) { // Usuario normal
            $totalClients = Client::active()->where('co_ven', $user->co_ven)->count();
            // Como no hay timestamps, simulamos "nuevos este mes" como los últimos registrados
            $newClientsThisMonth = Client::active()
                ->where('co_ven', $user->co_ven)
                ->orderBy('co_cli', 'desc')
                ->limit(5) // Últimos 5 como "nuevos"
                ->count();
        } else { // Admin
            $totalClients = Client::active()->count();
            // Para admin, también simulamos como los últimos registrados
            $newClientsThisMonth = Client::active()
                ->orderBy('co_cli', 'desc')
                ->limit(10) // Últimos 10 como "nuevos"
                ->count();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'clients' => [
                    'total' => $totalClients,
                    'new_this_month' => $newClientsThisMonth,
                    'is_admin' => $user->rol != 0
                ]
            ]
        ]);
    }
}
