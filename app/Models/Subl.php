<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subl extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'sub_lin';
    protected $primaryKey = 'co_subl';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_subl',
        'subl_des'
    ];


    public function articles()
    {
        return $this->hasMany(Article::class, 'co_subl', 'co_subl');
    }
}
