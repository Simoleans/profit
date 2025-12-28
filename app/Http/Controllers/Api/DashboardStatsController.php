<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Article;
use App\Models\Header;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\FacturaService;
use App\Services\CuentasXCobrarService;
use App\Services\ClienteService;

class DashboardStatsController extends Controller
{
    protected $facturaService;
    protected $cuentasXCobrarService;
    protected $clienteService;

    public function __construct(FacturaService $facturaService, CuentasXCobrarService $cuentasXCobrarService, ClienteService $clienteService)
    {
        $this->facturaService = $facturaService;
        $this->cuentasXCobrarService = $cuentasXCobrarService;
        $this->clienteService = $clienteService;
    }

    /**
     * Obtener estadísticas de clientes
     */
    public function clients()
    {
        $user = Auth::user();

        if ($user->rol == 0) {
            $totalClients = Client::active()->where('co_ven', $user->co_ven)->count();
            /* $newClientsThisMonth = Client::active()
                ->where('co_ven', $user->co_ven)
                ->orderBy('co_cli', 'desc')
                ->limit(5)
                ->count(); */
        } else {
            $totalClients = Client::active()->count();
            /* $newClientsThisMonth = Client::active()
                ->orderBy('co_cli', 'desc')
                ->limit(10)
                ->count(); */
        }

        return response()->json([
            'total' => $totalClients,
            //'new_this_month' => $newClientsThisMonth,
            'is_admin' => $user->rol != 0
        ]);
    }

    /**
     * Obtener estadísticas de retenciones/facturas
     */
    public function retenciones()
    {
        $user = Auth::user();
        $totalFacturas = $this->facturaService->obtenerTotalFacturas($user->co_ven);

        return response()->json([
            'total' => $totalFacturas,
            'codigo_vendedor' => $user->co_ven
        ]);
    }

    /**
     * Obtener estadísticas de cuentas por cobrar
     * Usa 3 queries optimizadas para total, vencidas y por vencer
     */
    public function cuentasPorCobrar()
    {
        $user = Auth::user();
        $vendedor = $user->rol == 0 ? $user->co_ven : null;

        //  Resumen total
        $resumenTotal = $this->cuentasXCobrarService->obtenerResumenTotal($vendedor);

        // Resumen vencidas
        $resumenVencidas = $this->cuentasXCobrarService->obtenerResumenVencidas($vendedor);

        // Resumen por vencer
        $resumenPorVencer = $this->cuentasXCobrarService->obtenerResumenPorVencer($vendedor);

        return response()->json([
            'total' => $resumenTotal['total'],
            'saldo_total' => $resumenTotal['saldo'],
            'vencidas' => $resumenVencidas['vencidas'],
            'saldo_vencido' => $resumenVencidas['saldo_vencido'],
            'por_vencer' => $resumenPorVencer['por_vencer'],
            'saldo_por_vencer' => $resumenPorVencer['saldo_por_vencer'],
            'codigo_vendedor' => $user->co_ven,
            'is_admin' => $user->rol != 0
        ]);
    }

    /**
     * Obtener artículos en promoción (co_cat = 9)
     */
    public function promotionArticles()
    {
        $cacheKey = 'promotion_articles';

        // Cachear por 15 minutos
        $articles = Cache::remember($cacheKey, 900, function() {
            return Article::with(['line', 'category'])
                ->where('co_cat', '9')
                ->where('stock_act', '>', 0)
                ->orderBy('art_des')
                ->limit(35)
                ->get(['co_art', 'art_des', 'prec_vta1', 'stock_act', 'co_lin', 'co_cat', 'uni_venta']);
        });

        return response()->json($articles);
    }

    /**
     * Obtener estadísticas de pedidos por estatus del mes actual
     */
    public function orderStats()
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Usar Eloquent con el modelo Header
        $stats = Header::selectRaw('
                status,
                COUNT(*) as total,
                SUM(tot_neto) as monto_total
            ')
            ->where('co_ven', $user->co_ven)
            ->whereMonth('fec_emis', $currentMonth)
            ->whereYear('fec_emis', $currentYear)
            ->where('anulada', 0)
            ->groupBy('status')
            ->get();

        // Organizar por status
        $organized = [
            'P' => ['total' => 0, 'monto' => 0, 'label' => 'Pendientes'],
            'A' => ['total' => 0, 'monto' => 0, 'label' => 'Aprobados'],
            'R' => ['total' => 0, 'monto' => 0, 'label' => 'Rechazados'],
            'F' => ['total' => 0, 'monto' => 0, 'label' => 'Facturados'],
        ];

        foreach ($stats as $stat) {
            $status = trim($stat->status);
            if (isset($organized[$status])) {
                $organized[$status]['total'] = $stat->total;
                $organized[$status]['monto'] = round($stat->monto_total, 2);
            }
        }

        return response()->json([
            'stats' => $organized,
            'month' => now()->format('F Y'),
            'codigo_vendedor' => $user->co_ven
        ]);
    }

    /**
     * Obtener clientes sin pedidos
     */
    public function clientesSinPedidos()
    {
        $user = Auth::user();

        // Si es administrador, traer todos los clientes sin pedidos
        // Si es vendedor, solo los suyos
        $vendedor = $user->rol == 0 ? $user->co_ven : null;

        // Clave de caché única por vendedor
        $cacheKey = "clientes_sin_pedidos_vendedor_{$vendedor}";

        // Cachear por 8 minutos
        $clientes = Cache::remember($cacheKey, 480, function() use ($vendedor) {
            return $this->clienteService->obtenerClientesSinPedidos($vendedor);
        });

        return response()->json([
            'clientes' => $clientes,
            'total' => $clientes->count(),
            'codigo_vendedor' => $user->co_ven,
            'is_admin' => $user->rol != 0
        ]);
    }

    /**
     * Obtener clientes inactivos (más de 3 meses sin comprar)
     */
    public function clientesInactivos(Request $request)
    {
        $user = Auth::user();

        // Si es administrador, traer todos los clientes inactivos
        // Si es vendedor, solo los suyos
        $vendedor = $user->rol == 0 ? $user->co_ven : null;

        // Obtener parámetro de paginación (por defecto 10 items por página)
        $perPage = $request->input('per_page', 10);

        $clientes = $this->clienteService->obtenerClientesSinVentasPor3Meses($vendedor, $perPage);

        return response()->json($clientes);
    }
}

