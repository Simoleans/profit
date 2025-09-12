<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Usar conexión MySQL para usuarios
    protected $connection = 'mysql';
    protected $table = 'users';
    protected $primaryKey = 'co_ven';
    public $timestamps = false; // Deshabilitar si no existen created_at/updated_at
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'co_ven';
    }

    /**
     * Get the column name for the "username".
     *
     * @return string
     */
    public function username()
    {
        return 'co_ven';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'co_ven',
        'name',
        'password',
        'rol',
        'email' // Agregar email si existe en la tabla
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
