<?php

namespace App\Http\Controllers;

use App\Models\Header;
use App\Models\Row;
use App\Models\{Client, User, Article};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HeaderController extends Controller
{
    /**
     * Limpiar agresivamente una cadena para evitar problemas de truncado
     */
    private function cleanString($value, $maxLength)
    {
        if (is_null($value)) {
            return '';
        }

        // Convertir a string, limpiar espacios, caracteres especiales
        $cleaned = trim($value);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Múltiples espacios a uno solo
        $cleaned = substr($cleaned, 0, $maxLength);
        $cleaned = trim($cleaned);

        return $cleaned;
    }

    /**
     * Limpiar código de artículo específicamente
     */
    private function cleanArticleCode($value, $maxLength = 8)
    {
        if (is_null($value)) {
            return '';
        }

        $cleaned = trim($value);
        // Si empieza con punto, reemplazarlo con guión para evitar problemas
        if (substr($cleaned, 0, 1) === '.') {
            $cleaned = '-' . substr($cleaned, 1);
        }
        $cleaned = substr($cleaned, 0, $maxLength);
        $cleaned = trim($cleaned);

        return $cleaned;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Header::with(['client', 'rows']) // Quitar seller porque está en otra conexión
            ->where('co_ven', Auth::user()->co_ven);


        // Aplicar filtro de búsqueda si existe
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('fact_num', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('cli_des', 'like', "%{$search}%")
                                  ->orWhere('co_cli', 'like', "%{$search}%");
                  });
            });
        }

        $headers = $query->orderBy('fact_num', 'desc')->paginate(15);

        // Cargar información de vendedores manualmente desde MySQL
        $ordersWithSellers = $headers->getCollection()->map(function ($order) {
            $seller = User::where('co_ven', $order->co_ven)->first();
            $order->seller = $seller;
            return $order;
        });

        $headers->setCollection($ordersWithSellers);

        return Inertia::render('Orders/Index', [
            'orders' => $headers,
            'search' => $request->get('search', '')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return Inertia::render('Orders/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'co_cli' => 'required|string|max:10', // Temporalmente sin exists para debugging
            'fec_emis' => 'required|date',
            'fec_venc' => 'required|date|after_or_equal:fec_emis',
            'descrip' => 'required|string|max:60', // varchar(60) NOT NULL
            'comentario' => 'nullable|string', // text NULL
            'dir_ent' => 'nullable|string', // text NOT NULL (pero puede ser vacío)
            'rows' => 'required|array|min:1',
            'rows.*.co_art' => 'required|string|max:30', // Temporalmente sin exists para debugging
            'rows.*.total_art' => 'required|numeric|min:0.01',
            'rows.*.prec_vta' => 'required|numeric|min:0',
            'rows.*.uni_venta' => 'nullable|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Generar próximo número de factura correlativo (dentro de la transacción)
            $fact_num = Header::getNextFactNum();

            // Calcular totales
            $tot_bruto = 0;
            $tot_neto = 0;

            foreach ($request->rows as $row) {
                $line_total = $row['total_art'] * $row['prec_vta'];
                $tot_bruto += $line_total;
                $tot_neto += $line_total; // Ajustar según lógica de negocio
            }

            $iva = $tot_neto * 0.16; // Ajustar según configuración

            $fecEmis = \Carbon\Carbon::createFromFormat('Y-m-d', $request->fec_emis)
            ->startOfDay()
            ->format('Y-m-d\TH:i:s'); // <- ISO 8601 con T

            $fecVenc = \Carbon\Carbon::createFromFormat('Y-m-d', $request->fec_venc)
                        ->startOfDay()
                        ->format('Y-m-d\TH:i:s');



            // Crear encabezado
            $header = Header::create([
                'fact_num' => $fact_num, // Correlativo automático 1, 2, 3, etc.
                'co_cli' => trim($request->co_cli), // Usar el código tal como viene
                'co_ven' => Auth::user()->co_ven, // Usar el código tal como viene
                'fec_emis'  => $fecEmis,
                'fec_venc'  => $fecVenc,
                'tot_bruto' => $tot_bruto,
                'tot_neto' => $tot_neto,
                'iva' => $iva,
                'status' => 'P', // Pendiente - char(1)
                'descrip' => substr($request->descrip, 0, 60), // varchar(60)
                'comentario' => $request->comentario ?? '', // text - sin límite
                'dir_ent' => $request->dir_ent ?? '', // text - requerido
                'anulada' => false
            ]);

            // Crear renglones
            foreach ($request->rows as $index => $row) {
                //$cleanCoArt = $this->cleanArticleCode($row['co_art'], 8); // Usar función específica para artículos
                $cleanUniVenta = $this->cleanString($row['uni_venta'] ?? 'UND', 3); // Usar función de limpieza

                // Log para debugging - todos los campos
                \Log::info('Creando renglón', [
                    'fact_num' => $fact_num,
                    'reng_num' => $index + 1,
                    'co_art_original' => $row['co_art'],
                    'co_art_clean' => $row['co_art'],
                    'co_art_length' => strlen($row['co_art']),
                    'total_art' => floatval($row['total_art']),
                    'prec_vta' => floatval($row['prec_vta']),
                    'reng_neto' => floatval($row['total_art']) * floatval($row['prec_vta']),
                    'tipo_imp' => 'IVA',
                    'tipo_imp_length' => strlen('IVA'),
                    'uni_venta_original' => $row['uni_venta'] ?? 'UND',
                    'uni_venta_clean' => $cleanUniVenta,
                    'uni_venta_length' => strlen($cleanUniVenta)
                ]);

                Row::create([
                    'fact_num' => $fact_num,
                    'reng_num' => $index + 1,
                    'co_art' => trim($row['co_art']),
                    'total_art' => floatval($row['total_art']),
                    'prec_vta' => floatval($row['prec_vta']),
                    'reng_neto' => floatval($row['total_art']) * floatval($row['prec_vta']),
                    'tipo_imp' => 'I', // Solo 1 caracter
                    'uni_venta' => substr($this->cleanString($cleanUniVenta, 2), 0, 1) // Ultra conservador - 1 caracter
                ]);
            }

            DB::commit();

            return redirect()->route('orders.index')->with('success', "Pedido #{$fact_num} creado exitosamente");

        } catch (\Exception $e) {
            DB::rollback();

            // Si es un error de clave foránea, dar más información
            if (str_contains($e->getMessage(), 'FK_encabezado_clientes')) {
                return back()->withErrors(['error' => 'El cliente seleccionado no existe o no está disponible para este vendedor.'])->withInput();
            }

            // Si es un error de truncado en renglones
            if (str_contains($e->getMessage(), 'renglones') && str_contains($e->getMessage(), 'truncar')) {
                // Mostrar el error completo para debugging
                return back()->withErrors(['error' => 'Error de truncado en renglones: ' . $e->getMessage()])->withInput();
            }

            return back()->withErrors(['error' => 'Error al crear el pedido: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($fact_num)
    {
        // Buscar el pedido directamente por fact_num y vendedor
        $header = Header::where('fact_num', $fact_num)
            ->where('co_ven', Auth::user()->co_ven)
            ->firstOrFail();

        // Ya no necesitamos verificar permisos porque filtramos por vendedor

        $header->load(['client', 'rows.article']); // Quitar seller

        // Cargar vendedor manualmente desde MySQL
        $seller = User::where('co_ven', $header->co_ven)->first();
        $header->seller = $seller;

        return Inertia::render('Orders/Show', [
            'order' => $header
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($fact_num)
    {
        // Buscar el pedido directamente por fact_num y vendedor
        $header = Header::where('fact_num', $fact_num)
            ->where('co_ven', Auth::user()->co_ven)
            ->firstOrFail();

        // Solo permitir edición si no está anulado
        if ($header->anulada) {
            return back()->withErrors(['error' => 'No se puede editar un pedido anulado']);
        }

        $header->load(['client', 'rows.article']); // Quitar seller

        // Cargar vendedor manualmente desde MySQL
        $seller = User::where('co_ven', $header->co_ven)->first();
        $header->seller = $seller;

        return Inertia::render('Orders/Edit', [
            'order' => $header
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $fact_num)
    {
        // Buscar el pedido directamente por fact_num y vendedor
        $header = Header::where('fact_num', $fact_num)
            ->where('co_ven', Auth::user()->co_ven)
            ->firstOrFail();

        if ($header->anulada) {
            return back()->withErrors(['error' => 'No se puede actualizar un pedido anulado']);
        }

        $request->validate([
            'co_cli' => 'required|string|max:10', // Temporalmente sin exists para debugging
            'fec_emis' => 'required|date',
            'fec_venc' => 'required|date|after_or_equal:fec_emis',
            'descrip' => 'required|string|max:60', // varchar(60) NOT NULL
            'comentario' => 'nullable|string', // text NULL
            'dir_ent' => 'nullable|string', // text NOT NULL (pero puede ser vacío)
            'rows' => 'required|array|min:1',
            'rows.*.co_art' => 'required|string|max:30', // Temporalmente sin exists para debugging
            'rows.*.total_art' => 'required|numeric|min:0.01',
            'rows.*.prec_vta' => 'required|numeric|min:0',
            'rows.*.uni_venta' => 'nullable|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Eliminar renglones existentes
            Row::where('fact_num', $header->fact_num)->delete();

            // Calcular totales
            $tot_bruto = 0;
            $tot_neto = 0;

            foreach ($request->rows as $row) {
                $line_total = $row['total_art'] * $row['prec_vta'];
                $tot_bruto += $line_total;
                $tot_neto += $line_total;
            }

            $iva = $tot_neto * 0.16;

            // Actualizar encabezado
            $header->update([
                'co_cli' => trim($request->co_cli), // Usar el código tal como viene
                'fec_emis' => $request->fec_emis,
                'fec_venc' => $request->fec_venc,
                'tot_bruto' => $tot_bruto,
                'tot_neto' => $tot_neto,
                'iva' => $iva,
                'descrip' => substr($request->descrip, 0, 60),
                'comentario' => $request->comentario,
                'dir_ent' => $request->dir_ent ?? '',
            ]);

            // Crear nuevos renglones
            foreach ($request->rows as $index => $row) {
                Row::create([
                    'fact_num' => $header->fact_num,
                    'reng_num' => $index + 1,
                    'co_art' => trim($row['co_art']), // Ultra conservador - 6 caracteres max
                    'total_art' => floatval($row['total_art']),
                    'prec_vta' => floatval($row['prec_vta']),
                    'reng_neto' => floatval($row['total_art']) * floatval($row['prec_vta']),
                    'tipo_imp' => 'I', // Solo 1 caracter
                    'uni_venta' => substr($this->cleanString($row['uni_venta'] ?? 'UND', 2), 0, 1) // Ultra conservador - 1 caracter
                ]);
            }

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Pedido actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al actualizar el pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($fact_num)
    {
        // Buscar el pedido directamente por fact_num y vendedor
        $header = Header::where('fact_num', $fact_num)
            ->where('co_ven', Auth::user()->co_ven)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Anular en lugar de eliminar (soft delete lógico)
            $header->update(['anulada' => true]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Pedido anulado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al anular el pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Search clients for autocomplete
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');

        // Temporalmente sin restricción de vendedor para debugging
        $clients = Client::where(function($q) use ($query) {
                $q->where('cli_des', 'like', "%{$query}%")
                  ->orWhere('co_cli', 'like', "%{$query}%");
            })
            ->where('co_cli','!=','')
            ->limit(20)
            ->get(['co_cli', 'cli_des', 'rif', 'co_ven']);

        return response()->json($clients);
    }

    /**
     * Verificar si un cliente existe
     */
    public function checkClient(Request $request)
    {
        $co_cli = $request->get('co_cli');

        $client = Client::where('co_cli', $co_cli)
            ->where('co_ven', Auth::user()->co_ven)
            ->first();

        return response()->json([
            'exists' => $client !== null,
            'client' => $client,
            'user_co_ven' => Auth::user()->co_ven
        ]);
    }

    /**
     * Debug temporal para verificar datos de usuario y pedido
     */
    public function debugUserData(Request $request)
    {
        $user = Auth::user();
        $factNum = $request->get('fact_num');

        $header = null;
        if ($factNum) {
            $header = Header::where('fact_num', $factNum)->first();
        }

        return response()->json([
            'user_co_ven' => $user->co_ven,
            'user_co_ven_trimmed' => trim($user->co_ven),
            'user_co_ven_length' => strlen($user->co_ven),
            'user_connection' => $user->getConnectionName(),
            'header_co_ven' => $header ? $header->co_ven : null,
            'header_co_ven_trimmed' => $header ? trim($header->co_ven) : null,
            'header_co_ven_length' => $header ? strlen($header->co_ven) : null,
            'header_connection' => $header ? $header->getConnectionName() : null,
            'comparison_result' => $header ? (trim($user->co_ven) == trim($header->co_ven)) : null
        ]);
    }

    /**
     * Search articles for autocomplete
     */
    public function searchArticles(Request $request)
    {
        $query = $request->get('q', '');

        $articles = Article::where(function($q) use ($query) {
                $q->where('art_des', 'like', "%{$query}%")
                  ->orWhere('co_art', 'like', "%{$query}%");
            })
            ->where('stock_act', '>', 0) // Solo artículos con stock
            ->limit(20)
            ->get(['co_art', 'art_des', 'uni_venta', 'prec_vta1', 'stock_act']);

        return response()->json($articles);
    }
}
