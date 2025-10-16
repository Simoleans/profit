<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CuentasXCobrarService
{
    /**
     * Obtener el saldo total de cuentas por cobrar (resumido).
     *
     * @param string $cliente
     * @param string $vendedor
     * @return \Illuminate\Support\Collection
     */
    /* public function obtenerCuentasXCobrarResumido($cliente = null, $vendedor = null)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('clientes as cli', 'dcc.co_cli', '=', 'cli.co_cli')
            ->select(
                'dcc.co_cli',
                'cli.cli_Des',
                DB::raw('SUM(CASE WHEN dcc.tipo_doc IN (\'FACT\', \'AJPM\', \'AJPA\', \'N/DB\') THEN 1 ELSE -1 END) * ROUND(dcc.saldo / dcc.tasa, 2) AS saldo')
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0');

        if ($cliente) {
            $query->where('dcc.co_cli', $cliente);
        }

        if ($vendedor) {
            $query->where('dcc.co_ven', $vendedor);
        }

        return $query->groupBy('dcc.co_cli', 'cli.cli_Des')
            ->orderBy('cli.cli_Des')
            ->get();
    } */

    public function obtenerCuentasXCobrarResumido($cliente = null, $vendedor = null)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('clientes as cli', 'dcc.co_cli', '=', 'cli.co_cli')
            ->select(
                'dcc.co_cli',
                'cli.cli_des',
                DB::raw("SUM(
                            CASE
                                WHEN dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB') THEN 1
                                ELSE -1
                            END * ROUND(dcc.saldo / dcc.tasa, 2)
                        ) AS saldo")
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->where('dcc.moneda', 'US$');

        // Filtro por cliente (opcional)
        if ($cliente) {
            $query->where('dcc.co_cli', $cliente);
        }

        // Filtro por vendedor (usuario en sesión)
        if ($vendedor) {
            $query->where('dcc.co_ven', $vendedor);
        }

        return $query->groupBy('dcc.co_cli', 'cli.cli_des')
            ->havingRaw("SUM(
                            CASE
                                WHEN dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB') THEN 1
                                ELSE -1
                            END * ROUND(dcc.saldo / dcc.tasa, 2)
                        ) > 0")
            ->orderBy('cli.cli_des')
            ->limit(10)
            ->get();
    }




    /**
     * Obtener el detalle de las cuentas por cobrar.
     *
     * @param string $cliente
     * @param string $vendedor
     * @return \Illuminate\Support\Collection
     */
    public function obtenerCuentasXCobrarDetallado($cliente = null, $vendedor = null)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('clientes as cli', 'dcc.co_cli', '=', 'cli.co_cli')
            ->select(
                'dcc.co_ven',
                'dcc.tipo_doc',
                'dcc.nro_doc',
                'dcc.co_cli',
                'cli.cli_Des',
                'dcc.fec_emis',
                DB::raw("CASE
                            WHEN dcc.tipo_doc = 'FACT' THEN
                                CASE
                                    WHEN dcc.campo3 = '' THEN CONVERT(CHAR(10), dcc.fec_venc, 103)
                                    ELSE TRY_CONVERT(CHAR(10), dcc.campo3, 103)
                                END
                            ELSE CONVERT(CHAR(10), dcc.fec_venc, 103)
                        END AS fec_entrega"),
                DB::raw('DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc) AS dias_cred'),
                DB::raw("DATEADD(DD,
                            DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc),
                            CONVERT(SMALLDATETIME, (
                                CASE
                                    WHEN dcc.tipo_doc = 'FACT' THEN
                                        CASE
                                            WHEN dcc.campo3 = '' THEN CONVERT(CHAR(10), dcc.fec_venc, 103)
                                            ELSE TRY_CONVERT(CHAR(10), dcc.campo3, 103)
                                        END
                                    ELSE CONVERT(CHAR(10), dcc.fec_venc, 103)
                                END
                            ), 103)
                        ) AS fec_venc"),
                'dcc.observa',
                'dcc.tasa',
                'dcc.moneda',
                DB::raw("CASE WHEN dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB') THEN 1 ELSE -1 END * ROUND(CASE WHEN dcc.co_sucu = '002' THEN dcc.monto_bru ELSE dcc.monto_net END / dcc.tasa, 2) AS monto_net"),
                DB::raw("CASE WHEN dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB') THEN 1 ELSE -1 END * ROUND(dcc.saldo / dcc.tasa, 2) AS saldo")
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->where('dcc.moneda', 'US$');

        if ($cliente) {
            $query->where('dcc.co_cli', $cliente);
        }

        if ($vendedor) {
            $query->where('dcc.co_ven', $vendedor);
        }

        return $query->orderBy('dcc.co_ven')
            ->orderBy('dcc.co_cli')
            ->orderBy('dcc.fec_emis')
            ->limit(1000)
            ->get();
    }

    /**
     * Obtener el conteo de facturas vencidas.
     *
     * @param string $vendedor
     * @return int
     */
    public function obtenerFacturasVencidas($vendedor)
    {
        $result = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('clientes as cli', 'dcc.co_cli', '=', 'cli.co_cli')
            ->select(DB::raw('COUNT(dcc.nro_doc) AS CUANTOS'))
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->whereRaw('DATEDIFF(DD, DATEADD(DD, DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc), CONVERT(SMALLDATETIME, (CASE WHEN dcc.tipo_doc = \'FACT\' THEN
                            CASE WHEN (SELECT campo3 FROM factura WHERE fact_num = dcc.nro_doc) = \'\'
                            THEN CONVERT(CHAR(10), dcc.fec_venc, 103)
                            ELSE (SELECT CONVERT(CHAR(10), campo3, 121) FROM factura WHERE fact_num = dcc.nro_doc)
                            END ELSE CONVERT(CHAR(10), dcc.fec_venc, 103) END), 103)) , GETDATE()) > 0')
            ->where('dcc.tipo_doc', 'FACT')
            ->where('dcc.co_ven', $vendedor)
            ->first();

        return $result->CUANTOS ?? 0;
    }

    /**
     * Obtener el conteo de facturas por vencer.
     *
     * @param string $vendedor
     * @return int
     */
    public function obtenerFacturasPorVencer($vendedor)
    {
        $result = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('clientes as cli', 'dcc.co_cli', '=', 'cli.co_cli')
            ->select(DB::raw('COUNT(dcc.nro_doc) AS CUANTOS'))
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->whereRaw('DATEDIFF(DD, DATEADD(DD, DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc), CONVERT(SMALLDATETIME, (CASE WHEN dcc.tipo_doc = \'FACT\' THEN
                            CASE WHEN (SELECT campo3 FROM factura WHERE fact_num = dcc.nro_doc) = \'\'
                            THEN CONVERT(CHAR(10), dcc.fec_venc, 103)
                            ELSE (SELECT CONVERT(CHAR(10), campo3, 121) FROM factura WHERE fact_num = dcc.nro_doc)
                            END ELSE CONVERT(CHAR(10), dcc.fec_venc, 103) END), 103)) , GETDATE()) <= 0')
            ->where('dcc.tipo_doc', 'FACT')
            ->where('dcc.co_ven', $vendedor)
            ->first();

        return $result->CUANTOS ?? 0;
    }
}
