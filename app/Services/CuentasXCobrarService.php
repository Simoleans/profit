<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ->orderBy('saldo', 'desc')
            ->limit(100)
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
     * Obtener resumen total de CxC (Query 1 optimizado)
     * Retorna el conteo de facturas y el saldo total
     *
     * @param string|null $vendedor
     * @return array
     */
    public function obtenerResumenTotal($vendedor = null, $supervisor = null,$user)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('vendedor as ven', 'dcc.co_ven', '=', 'ven.co_ven')
            ->select(
                DB::raw("COUNT(IIF(dcc.tipo_doc = 'FACT', 1, 0)) AS cuantas_facturas"),
                DB::raw("
                    SUM(
                        IIF(dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB'), 1, -1)
                        * ROUND(dcc.saldo / dcc.tasa, 2)
                    ) AS saldo
                ")
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->where('dcc.moneda', 'US$')
            ->whereRaw('YEAR(dcc.fec_emis) >= 2024');

            $coVen = trim((string) $user->co_ven);

            if ($vendedor) {
                $query->whereRaw('LTRIM(RTRIM(dcc.co_ven)) = ?', [$coVen]);
             }

             if ($supervisor) {
                $query->whereRaw('LTRIM(RTRIM(ven.campo1)) = ?', [$coVen]);
             }


        $result = $query->first();

        return [
            'total' => (int) ($result->cuantas_facturas ?? 0),
            'saldo' => (float) ($result->saldo ?? 0),
        ];
    }


    /**
     * Obtener resumen de CxC vencidas (Query 2 optimizado)
     * Retorna el conteo de facturas vencidas y el saldo vencido
     *
     * @param string|null $vendedor
     * @return array
     */
    public function obtenerResumenVencidas($vendedor = null, $supervisor = null, $user)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('vendedor as ven', 'dcc.co_ven', '=', 'ven.co_ven')
            ->select(
                DB::raw("COUNT(IIF(dcc.tipo_doc = 'FACT', 1, 0)) AS cuantas_facturas"),
                DB::raw("
                    SUM(
                        IIF(dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB'), 1, -1)
                        * ROUND(dcc.saldo / dcc.tasa, 2)
                    ) AS saldo
                ")
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->whereRaw("
                DATEADD(dd,
                    DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc),
                    CONVERT(SMALLDATETIME, (
                        IIF(dcc.tipo_doc = 'FACT',
                            IIF(dcc.campo3 = '', CONVERT(CHAR(10), dcc.fec_venc, 103), TRY_CONVERT(CHAR(10), dcc.campo3, 103)),
                            CONVERT(CHAR(10), dcc.fec_venc, 103)
                        )
                    ), 103)
                ) < GETDATE()
            ")
            ->where('dcc.moneda', 'US$')
            ->whereRaw('YEAR(dcc.fec_emis) >= 2024');

        $coVen = trim((string) $user->co_ven);

        if ($vendedor) {
            $query->whereRaw('LTRIM(RTRIM(dcc.co_ven)) = ?', [$coVen]);
        }

        if ($supervisor) {
            $query->whereRaw('LTRIM(RTRIM(ven.campo1)) = ?', [$coVen]);
        }

        $result = $query->first();

        return [
            'vencidas' => (int) ($result->cuantas_facturas ?? 0),
            'saldo_vencido' => (float) ($result->saldo ?? 0),
        ];
    }

    /**
     * Obtener resumen de CxC por vencer (Query 3 optimizado)
     * Retorna el conteo de facturas por vencer y el saldo por vencer
     *
     * @param string|null $vendedor
     * @return array
     */
    public function obtenerResumenPorVencer($vendedor = null, $supervisor = null, $user)
    {
        $query = DB::connection('factura')
            ->table('docum_cc as dcc')
            ->join('vendedor as ven', 'dcc.co_ven', '=', 'ven.co_ven')
            ->select(
                DB::raw("COUNT(IIF(dcc.tipo_doc = 'FACT', 1, 0)) AS cuantas_facturas"),
                DB::raw("
                    SUM(
                        IIF(dcc.tipo_doc IN ('FACT', 'AJPM', 'AJPA', 'N/DB'), 1, -1)
                        * ROUND(dcc.saldo / dcc.tasa, 2)
                    ) AS saldo
                ")
            )
            ->whereRaw('ROUND(dcc.saldo / dcc.tasa, 2) > 0')
            ->whereRaw("
                DATEADD(dd,
                    DATEDIFF(DD, dcc.fec_emis, dcc.fec_venc),
                    CONVERT(SMALLDATETIME, (
                        IIF(dcc.tipo_doc = 'FACT',
                            IIF(dcc.campo3 = '', CONVERT(CHAR(10), dcc.fec_venc, 103), TRY_CONVERT(CHAR(10), dcc.campo3, 103)),
                            CONVERT(CHAR(10), dcc.fec_venc, 103)
                        )
                    ), 103)
                ) >= GETDATE()
            ")
            ->where('dcc.tipo_doc', 'FACT')
            ->where('dcc.moneda', 'US$')
            ->whereRaw('YEAR(dcc.fec_emis) >= 2024');

        $coVen = trim((string) $user->co_ven);

        if ($vendedor) {
            $query->whereRaw('LTRIM(RTRIM(dcc.co_ven)) = ?', [$coVen]);
        }

        if ($supervisor) {
            $query->whereRaw('LTRIM(RTRIM(ven.campo1)) = ?', [$coVen]);
        }

        $result = $query->first();

        return [
            'por_vencer' => (int) ($result->cuantas_facturas ?? 0),
            'saldo_por_vencer' => (float) ($result->saldo ?? 0),
        ];
    }

}
