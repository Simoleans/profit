<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients for the authenticated user.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $user = Auth::user();

        $clients = Client::query()
            ->where('co_ven', $user->co_ven) // Solo clientes del vendedor logueado
            ->when($search, function ($query, $search) {
                return $query->where('cli_des', 'like', "%{$search}%")
                           ->orWhere('co_cli', 'like', "%{$search}%");
            })
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();

        $client = Client::where('co_cli', $id)
                       ->where('co_ven', $user->co_ven) // Solo clientes del vendedor logueado
                       ->firstOrFail();

        return response()->json($client);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
