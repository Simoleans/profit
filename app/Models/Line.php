<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'lin_art';
    protected $primaryKey = 'co_lin';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_lin',
        'lin_des'
    ];

    /**
     * Relación con artículos (hasMany)
     * Una línea puede tener muchos artículos
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'co_lin', 'co_lin');
    }
}
