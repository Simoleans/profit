<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTemp extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'clientes_temp';
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
        'co_ven',
        'status'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'co_ven', 'co_ven');
    }

    // Scope para clientes temporales de usuarios normales
    public function scopeClientTempWithUser($query, $search)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $clients = ClientTemp::query()
            ->where('co_ven', $user->co_ven)
            ->when($search, function ($query, $search) {
                return $query->where('cli_des', 'like', "%{$search}%")
                           ->orWhere('co_cli', 'like', "%{$search}%");
            })
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();

        return $clients;
    }

    // Scope para clientes temporales de administradores
    public function scopeClientTempWithAdmin($query, $search)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $clients = ClientTemp::query()
            ->when($search, function ($query, $search) {
                return $query->where('cli_des', 'like', "%{$search}%")
                           ->orWhere('co_cli', 'like', "%{$search}%");
            })
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();

        return $clients;
    }
}
