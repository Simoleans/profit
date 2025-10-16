<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Header extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'encabezado';
    protected $primaryKey = 'fact_num';
    public $timestamps = false;
    public $incrementing = false; // Usaremos correlativo manual
    protected $keyType = 'int';

    protected $fillable = [
        'fact_num',
        'co_cli',
        'co_ven',
        'fec_emis',
        'fec_venc',
        'tot_bruto',
        'tot_neto',
        'iva',
        'status',
        'comentario',
        'descrip',
        'dir_ent',
        'anulada'
    ];

    protected $casts = [
        'fec_emis' => 'datetime',
        'fec_venc' => 'datetime',
        'tot_bruto' => 'decimal:2',
        'tot_neto' => 'decimal:2',
        'iva' => 'decimal:2',
        'anulada' => 'boolean'
    ];

    protected $dateFormat = 'Y-m-d\TH:i:s'; // ISO con T (no ambiguo)

    // Accessor para co_cli sin espacios (igual que en Client)
    public function getCoCliAttribute($value)
    {
        return trim($value);
    }

    // Accessor para co_ven sin espacios
    public function getCoVenAttribute($value)
    {
        return trim($value);
    }

    // Relación con renglones
    public function rows()
    {
        return $this->hasMany(Row::class, 'fact_num', 'fact_num');
    }

    // Relación con cliente
    public function client()
    {
        return $this->belongsTo(Client::class, 'co_cli', 'co_cli');
    }


    // Relación con vendedor (conexión cruzada MySQL)
    public function seller()
    {
        // No podemos usar belongsTo entre diferentes conexiones
        // Usar query manual para obtener el vendedor
        return \DB::connection('mysql')
            ->table('users')
            ->where('co_ven', $this->co_ven)
            ->first();
    }

    // Método auxiliar para obtener el vendedor como modelo
    public function getSellerAttribute()
    {
        return User::where('co_ven', $this->co_ven)->first();
    }

    /**
     * Generar el próximo número de factura correlativo
     * @return int
     */
    public static function getNextFactNum()
    {
        // Para SQL Server, usar una consulta más robusta
        return \DB::connection('sqlsrv')->selectOne(
            "SELECT ISNULL(MAX(fact_num), 0) + 1 as next_num FROM [encabezado] WITH (TABLOCKX)"
        )->next_num;
    }
}
