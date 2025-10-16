<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\ClientTemp;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Services\CuentasXCobrarService;

class ClientController extends Controller
{
    /**
     * Display a listing of clients for the authenticated user.
     */
    public function index(Request $request, CuentasXCobrarService $cuentasXCobrarService)
    {
        $search = $request->get('search', '');
        $tab = $request->get('tab', 'processed'); // 'processed', 'temp' o 'balance'
        $user = Auth::user();

        // Tab de clientes con saldo
        if($tab === 'balance') {
            $clientsData = $cuentasXCobrarService->obtenerCuentasXCobrarResumido(null, $user->co_ven);

            // Convertir a colección y aplicar filtro de búsqueda si existe
            if ($search) {
                $clientsData = $clientsData->filter(function ($client) use ($search) {
                    return stripos($client->co_cli, $search) !== false ||
                           stripos($client->cli_des, $search) !== false;
                });
            }

            // Simular estructura de paginación para mantener compatibilidad con la vista
            $clients = [
                'data' => $clientsData->values(),
                'total' => $clientsData->count(),
                'last_page' => 1,
                'from' => $clientsData->count() > 0 ? 1 : 0,
                'to' => $clientsData->count(),
                'links' => []
            ];
        }
        // Tabs de clientes procesados y temporales
        else {
            if($user->rol == 0){
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
    public function store(StoreClientRequest $request)
    {
        // Los datos ya vienen validados del StoreClientRequest
        $validated = $request->validated();

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

            // Manejar la carga del documento si existe
            if ($request->hasFile('document')) {
                $file = $request->file('document');

                // Generar nombre único para el archivo
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Guardar el archivo en storage/app/client-documents
                $path = $file->storeAs('client-documents', $fileName, 'local');

                // Crear registro en la tabla media usando la relación (automáticamente usa rif)
                $client->media()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'visibility' => 'private',
                ]);
            }

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

        $rif = urldecode(trim($id));

        try {
            if($tab === 'temp') {
                // Buscar en tabla temporal con relación media
                $client = ClientTemp::where('rif', $rif)->with('media')->firstOrFail();
            } else {
                // Buscar en tabla principal con relación media
                $client = Client::where('rif', $rif)->with('media')->firstOrFail();
            }

            return response()->json($client);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
    }

    /**
     * Descargar documento del cliente
     */
    public function downloadDocument(string $mediaId)
    {
        try {
            $media = Media::findOrFail($mediaId);

            // Obtener la ruta completa del archivo usando Storage
            $filePath = Storage::disk('local')->path($media->path);

            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Archivo no encontrado en: ' . $filePath], 404);
            }

            // Descargar el archivo
            return response()->download($filePath, $media->original_name);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al descargar el archivo: ' . $e->getMessage()], 500);
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
    public function update(UpdateClientRequest $request, string $id)
    {
        // Los datos ya vienen validados del UpdateClientRequest
        $validated = $request->validated();

        try {
            // Buscar el cliente por RIF en tabla temporal
            $rif = urldecode(trim($id));
            $client = ClientTemp::where('rif', $rif)->firstOrFail();

            // Actualizar el cliente en tabla temporal
            $client->update($validated);

            return redirect()->route('clients.index')->with('success', 'Cliente actualizado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Mostrar el detalle de cuentas por cobrar de un cliente específico
     */
    public function balanceDetail(string $co_cli, CuentasXCobrarService $cuentasXCobrarService)
    {
        $user = Auth::user();

        // Obtener el detalle de cuentas por cobrar para el cliente específico
        $balanceDetail = $cuentasXCobrarService->obtenerCuentasXCobrarDetallado($co_cli, $user->co_ven);

        // Obtener información del cliente (primer registro para obtener datos del cliente)
        $clientInfo = null;
        if ($balanceDetail->isNotEmpty()) {
            $clientInfo = [
                'co_cli' => $balanceDetail->first()->co_cli,
                'cli_des' => $balanceDetail->first()->cli_Des
            ];
        }

        return Inertia::render('Clients/BalanceDetail', [
            'client' => $clientInfo,
            'balanceDetail' => $balanceDetail,
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        try {
            // Buscar el cliente por RIF
            $rif = urldecode(trim($id));
            $client = Client::where('rif', $rif)->firstOrFail();

            $client->update(['status' => 0]);

            return redirect()->route('clients.index')->with('success', 'Cliente desactivado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al desactivar el cliente: ' . $e->getMessage()]);
        }
    }
}
