<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'cat_art';
    protected $primaryKey = 'co_cat';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_cat',
        'cat_des',
    ];


    public function articles()
    {
        return $this->hasMany(Article::class, 'co_cat', 'co_cat');
    }

}
