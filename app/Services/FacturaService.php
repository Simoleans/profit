<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FacturaService
{
    /**
     * Obtener el total de facturas.
     *
     * @param string $codigoVendedor
     * @return int
     */
    /* public function obtenerTotalFacturas($codigoVendedor, $supervisor, $user)
    {
        $result = DB::connection('factura')
                    ->selectOne("
                        select COUNT(FAC.fact_num) as cuantas
                        from factura fac
                        inner join clientes cli on fac.co_cli = cli.co_cli
                        where fact_num not in (select nro_orig from docum_cc where tipo_doc='AJNM' and campo8='IVA')
                        and fac.co_sucu = '001'
                        and fac.saldo > 0
                        and fac.co_ven = :codigoVendedor
                    ", ['codigoVendedor' => $codigoVendedor]);

        return $result->cuantas ?? 0;
    } */

    public function obtenerTotalFacturas($vendedor = null, $supervisor = null, $user)
    {
        $query = DB::connection('factura')
            ->table('factura as fac')
            ->join('clientes as cli', 'fac.co_cli', '=', 'cli.co_cli')
            ->join('vendedor as ven', 'fac.co_ven', '=', 'ven.co_ven')
            ->whereNotIn('fac.fact_num', function ($q) {
                $q->select('dcc.nro_orig')
                ->from('docum_cc as dcc')
                ->where('dcc.tipo_doc', 'AJNM')
                ->where('dcc.campo8', 'IVA');
            })
            ->where('fac.co_sucu', '001')
            ->where('fac.saldo', '>', 0);

        $coVen = trim((string) $user->co_ven);

        if ($vendedor) {
            $query->whereRaw('LTRIM(RTRIM(fac.co_ven)) = ?', [$coVen]);
        }

        if ($supervisor) {
            $query->whereRaw('LTRIM(RTRIM(ven.campo1)) = ?', [$coVen]);
        }

        return (int) $query->count('fac.fact_num');
    }


    /**
     * Obtener el detalle de las facturas.
     *
     * @param string $codigoVendedor
     * @return array
     */
    public function obtenerDetalleFacturas2($vendedor = null, $supervisor = null, $user)
    {
        $coVen = trim((string) $user->co_ven);
        $result = DB::connection('factura')
                    ->select("
                        select fac.fact_num, fac.fec_emis, fac.co_cli, cli.cli_des, fac.iva, fac.tot_neto
                        from factura fac
                        inner join clientes cli on fac.co_cli = cli.co_cli
                        where fact_num not in (select nro_orig from docum_cc where tipo_doc='AJNM' and campo8='IVA')
                        and fac.co_sucu = '001'
                        and fac.saldo > 0
                        and fac.co_ven = :codigoVendedor
                    ", ['codigoVendedor' => $coVen]);

        return $result;
    }

    public function obtenerDetalleFacturas($vendedor = null, $supervisor = null, $user)
    {
        $coVen = trim((string) $user->co_ven);

        $query = DB::connection('factura')
            ->table('factura as fac')
            ->join('clientes as cli', 'fac.co_cli', '=', 'cli.co_cli')
            ->join('vendedor as ven', 'fac.co_ven', '=', 'ven.co_ven')
            ->whereNotIn('fac.fact_num', function ($q) {
                $q->select('dcc.nro_orig')
                ->from('docum_cc as dcc')
                ->where('dcc.tipo_doc', 'AJNM')
                ->where('dcc.campo8', 'IVA');
            })
            ->where('fac.co_sucu', '001')
            ->where('fac.saldo', '>', 0);

        if ($vendedor) {
            $query->whereRaw('LTRIM(RTRIM(fac.co_ven)) = ?', [$coVen]);
        }

        if ($supervisor) {
            $query->whereRaw('LTRIM(RTRIM(ven.campo1)) = ?', [$coVen]);
        }

        return $query->select(
            'fac.fact_num',
            'fac.fec_emis',
            'fac.co_cli',
            'cli.cli_des',
            'fac.iva',
            'fac.tot_neto'
        )->get();
    }

}
