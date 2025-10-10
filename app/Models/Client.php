<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Client extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'clientes';
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
        'co_ven',
        'status'
    ];

    /* protected $casts = [
        'status' => 'boolean',
    ]; */

    // Relación con el vendedor/usuario
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'co_ven', 'co_ven');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Accessor para co_cli sin espacios
    public function getCoCliAttribute($value)
    {
        return trim($value);
    }

    // Relación con los medios usando co_cli como clave
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable', 'mediable_type', 'mediable_id', 'co_cli');
    }

    //querys
    public function scopeClientWithUser($query, $search)
    {
        $user = Auth::user();
        $clients = Client::query()
            ->where('co_ven', $user->co_ven) // Solo clientes del vendedor logueado
            ->when($search, function ($query, $search) {
                return $query->where('cli_des', 'like', "%{$search}%")
                           ->orWhere('co_cli', 'like', "%{$search}%");
            })
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();

        return $clients;
    }

    public function scopeClientWithUserAndAdmin($query, $search)
    {
        $user = Auth::user();
        $clients = Client::query()
            ->when($search, function ($query, $search) {
                return $query->where('cli_des', 'like', "%{$search}%")
                           ->orWhere('co_cli', 'like', "%{$search}%");
            })
            ->orderBy('cli_des')
            ->paginate(10)
            ->withQueryString();

        return $clients;
    }

    // Scope para clientes procesados (con co_cli no vacío) para usuarios normales
    public function scopeClientProcessedWithUser($query, $search)
    {
        $user = Auth::user();
        $clients = Client::query()
            ->where('co_cli', '!=', '')
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

    // Scope para clientes procesados (con co_cli no vacío) para administradores
    public function scopeClientProcessedWithAdmin($query, $search)
    {
        $user = Auth::user();
        $clients = Client::query()
            ->where('co_cli', '!=', '')
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
