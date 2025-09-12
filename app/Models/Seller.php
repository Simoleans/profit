<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'vendedor';
    protected $primaryKey = 'co_ven';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_ven',
        'ven_des',
    ];
}
