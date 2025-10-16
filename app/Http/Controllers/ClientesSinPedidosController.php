<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ClienteService;
use Illuminate\Support\Facades\Auth;

class ClientesSinPedidosController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Mostrar la vista de clientes sin pedidos
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Si es administrador, traer todos los clientes sin pedidos
        // Si es vendedor, solo los suyos
        $vendedor = $user->rol == 0 ? $user->co_ven : null;

        $clientes = $this->clienteService->obtenerClientesSinPedidos($vendedor);

        return Inertia::render('Clients/SinPedidos', [
            'clientes' => $clientes,
            'search' => $request->input('search', '')
        ]);
    }
}

