<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Header extends Model
{
    protected $table = 'dbo.encabezado';
    //protected $primaryKey = 'IdEncabezado'; // adjust PK
    public $timestamps = false;

    // Example relation: one header has many rows
    public function rows()
    {
        return $this->hasMany(Row::class, 'IdEncabezado');
    }
}
