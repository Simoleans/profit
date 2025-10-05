<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\ClientTemp;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients for the authenticated user.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $tab = $request->get('tab', 'processed'); // 'processed' o 'temp'

        if(Auth::user()->rol == 0){
            if($tab === 'temp') {
                $clients = ClientTemp::clientTempWithUser($search);
            } else {
                $clients = Client::clientProcessedWithUser($search);
            }
        }else{ //es admin
            if($tab === 'temp') {
                $clients = ClientTemp::clientTempWithAdmin($search);
            } else {
                $clients = Client::clientProcessedWithAdmin($search);
            }
        }

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'search' => $search,
            'activeTab' => $tab,
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
            'rif' => 'required|unique:sqlsrv.clientes_temp,rif',
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

        // Agregar campos automáticos
        $validated['co_cli'] = ''; // Co_cli vacío al crear en tabla temporal
        $validated['co_ven'] = $user->co_ven; // Código del vendedor que registra

        // Campos opcionales que pueden estar vacíos
        $validated['direc2'] = '';
        $validated['comentario'] = '';
        $validated['status'] = 1; // Cliente activo por defecto

        try {
            // Crear el cliente en tabla temporal
            $client = ClientTemp::create($validated);

            return redirect()->route('clients.index')->with('success', 'Cliente registrado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al registrar el cliente: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'processed'); // 'processed' o 'temp'

        $rif = urldecode(trim($id)); // Decodificar URL y limpiar espacios

        try {
            if($tab === 'temp') {
                // Buscar en tabla temporal
                $client = ClientTemp::where('rif', $rif)->firstOrFail();
            } else {
                // Buscar en tabla principal
                $client = Client::where('rif', $rif)->firstOrFail();
            }

            return response()->json($client);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
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
        // Validar los datos del formulario
        $validated = $request->validate([
            'cli_des' => 'required|string|max:60',
            'rif' => 'required|unique:sqlsrv.clientes_temp,rif,' . urldecode(trim($id)) . ',rif',
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

        try {
            // Buscar el cliente por RIF en tabla temporal
            $rif = urldecode(trim($id)); // Decodificar URL y limpiar espacios
            $client = ClientTemp::where('rif', $rif)->firstOrFail();

            // Actualizar el cliente en tabla temporal
            $client->update($validated);

            return redirect()->route('clients.index')->with('success', 'Cliente actualizado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        try {
            // Buscar el cliente por RIF
            $rif = urldecode(trim($id)); // Decodificar URL y limpiar espacios
            $client = Client::where('rif', $rif)->firstOrFail();

            // Soft delete: cambiar status a 0
            $client->update(['status' => 0]);

            return redirect()->route('clients.index')->with('success', 'Cliente desactivado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al desactivar el cliente: ' . $e->getMessage()]);
        }
    }
}
