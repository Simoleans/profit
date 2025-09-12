<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'dbo.clientes';
    protected $primaryKey = 'co_cli';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_cli',
        'cli_des',
        'direc1',
        'direc2',
        'telefonos',
        'comentario',
        'respons',
        'rif',
        'email',
        'ciudad',
        'co_ven'
    ];

    // Relación con el vendedor/usuario
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'co_ven', 'co_ven');
    }
}
