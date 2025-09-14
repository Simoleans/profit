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

        if(auth()->user()->rol == 1){
            $clients = Client::clientWithUser($search);
        }else{ //es admin
            $clients = Client::query()
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();
        }

        //dd($clients);

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
        // Validar los datos del formulario
        $validated = $request->validate([
            'cli_des' => 'required|string|max:60',
            'rif' => 'required|unique:sqlsrv.clientes,rif',
            'direc1' => 'required|string|max:120',
            'telefonos' => 'required|string|max:30',
            'respons' => 'required|string|max:60',
            'email' => 'required|email|max:120',
            'ciudad' => 'required|string|max:30',
        ], [
            'cli_des.required' => 'El nombre o razón social es obligatorio.',
            'cli_des.max' => 'El nombre no puede exceder 60 caracteres.',
            'rif.required' => 'El RIF/Cédula es obligatorio.',
            'rif.unique' => 'Este RIF/Cédula ya está registrado en el sistema.',
            'direc1.required' => 'La dirección es obligatoria.',
            'direc1.max' => 'La dirección no puede exceder 120 caracteres.',
            'telefonos.required' => 'Los teléfonos son obligatorios.',
            'telefonos.max' => 'Los teléfonos no pueden exceder 30 caracteres.',
            'respons.required' => 'El responsable es obligatorio.',
            'respons.max' => 'El responsable no puede exceder 60 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede exceder 120 caracteres.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.max' => 'La ciudad no puede exceder 30 caracteres.',
        ]);

        $user = Auth::user();

        // Generar el código de cliente automáticamente
        $lastClient = Client::orderBy('co_cli', 'desc')->first();
        $newClientCode = $lastClient ? (str_pad((int)$lastClient->co_cli + 1, 6, '0', STR_PAD_LEFT)) : '000001';

        // Agregar campos automáticos
        $validated['co_cli'] = $newClientCode;
        $validated['co_ven'] = $user->co_ven; // Código del vendedor que registra

        // Campos opcionales que pueden estar vacíos
        $validated['direc2'] = '';
        $validated['comentario'] = '';

        try {
            // Crear el cliente
            $client = Client::create($validated);

            return redirect()->route('clients.index')->with('success', 'Cliente registrado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al registrar el cliente: ' . $e->getMessage()])->withInput();
        }
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
