<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ClienteService
{
    /**
     * Obtener clientes sin pedidos.
     *
     * @param string|array|null $vendedor Código de vendedor o array de códigos
     * @return \Illuminate\Support\Collection
     */
    public function obtenerClientesSinPedidos($vendedor = null)
    {
        $query = DB::connection('factura')
            ->table('clientes as cli')
            ->join('vendedor as ven', 'cli.co_ven', '=', 'ven.co_ven')
            ->join('zona as zon', 'cli.co_zon', '=', 'zon.co_zon')
            ->select(
                'cli.co_cli',
                'cli.cli_des',
                'cli.fecha_reg',
                'cli.co_ven',
                'ven.ven_des',
                'zon.zon_des'
            )
            ->where('cli.inactivo', 0)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pedidos')
                    ->whereColumn('pedidos.co_cli', 'cli.co_cli')
                    ->where('pedidos.anulada', 0)
                    ->limit(1);
            });

        // Filtro por vendedor/supervisor (si aplica)
        if ($vendedor) {
            if (is_array($vendedor)) {
                $query->whereIn('cli.co_ven', $vendedor);
            } else {
                $query->where('cli.co_ven', $vendedor);
            }
        }

        return $query->orderBy('cli.cli_des')
            ->limit(50)
            ->get();
    }

    /**
     * Obtener clientes sin ventas por más de 3 meses con paginación
     * Ordenado por el promedio mensual de las ventas del último año desde la última fecha de compra
     *
     * @param string|array|null $vendedor Código de vendedor o array de códigos
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerClientesSinVentasPor3Meses($vendedor = null, $perPage = 10)
    {
        $query = DB::connection('factura')
            ->table('clientes as cli')
            ->join('factura as fac', 'cli.co_cli', '=', 'fac.co_cli')
            ->select(
                'cli.co_cli',
                'cli.cli_des',
                DB::raw('(SELECT TOP 1 fec_emis FROM factura WHERE co_cli = cli.co_cli ORDER BY fec_emis DESC) AS ult_fec_fac'),
                DB::raw('DATEDIFF(MM, (SELECT TOP 1 fec_emis FROM factura WHERE co_cli = cli.co_cli ORDER BY fec_emis DESC), GETDATE()) AS meses_ult_fac'),
                DB::raw('ROUND((SUM(fac.tot_bruto / fac.tasa) / 12), 2) AS prom_vta_mens')
            )
            ->where('fac.anulada', 0)
            ->where('fac.moneda', 'US$')
            ->where('cli.inactivo', 0)
            ->whereRaw('DATEDIFF(MM, (SELECT TOP 1 fec_emis FROM factura WHERE co_cli = cli.co_cli ORDER BY fec_emis DESC), GETDATE()) > 3');

        // Filtro por vendedor/supervisor (si aplica)
        if ($vendedor) {
            if (is_array($vendedor)) {
                $query->whereIn('cli.co_ven', $vendedor);
            } else {
                $query->where('cli.co_ven', $vendedor);
            }
        }

        return $query->groupBy('cli.co_cli', 'cli.cli_des')
            ->orderByDesc('prom_vta_mens')
            ->paginate($perPage);
    }
}

