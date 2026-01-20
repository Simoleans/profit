<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $table = 'supervisor';
    protected $primaryKey = 'co_sup';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_ven',
        'nombre_sup',
    ];
}
