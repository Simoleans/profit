<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'renglones';
    protected $primaryKey = null; // Clave compuesta
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'fact_num',
        'reng_num',
        'co_art',
        'total_art',
        'prec_vta',
        'reng_neto',
        'tipo_imp',
        'uni_venta'
    ];

    protected $casts = [
        'total_art' => 'decimal:2',
        'prec_vta' => 'decimal:2',
        'reng_neto' => 'decimal:2'
    ];

    // Relación con encabezado
    public function header()
    {
        return $this->belongsTo(Header::class, 'fact_num', 'fact_num');
    }

    // Relación con artículo
    public function article()
    {
        return $this->belongsTo(Article::class, 'co_art', 'co_art');
    }
}
