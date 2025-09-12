<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'art';
    protected $primaryKey = 'co_art';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_art',
        'art_des',
        'co_lin',
        'co_cat',
        'co_subl',
        'co_color',
        'modelo',
        'comentario',
        'uni_venta',
        'uni_compra',
        'uni_relac',
        'relac_aut',
        'stock_act',
        'stock_com',
        'stock_des',
        'prec_om',
        'prec_vta1',
        'prec_vta2',
        'prec_vta3',
        'prec_vta4',
        'prec_vta5'
    ];
}
