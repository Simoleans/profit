<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'dbo.clientes';
    protected $primaryKey = 'cod_cli'; // adjust to your real PK
    public $timestamps = false;
}
