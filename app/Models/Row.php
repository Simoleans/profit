<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    protected $table = 'dbo.renglones';
    //protected $primaryKey = 'IdRenglon'; // adjust PK
    public $timestamps = false;

    // Example relation: one row belongs to a header
    public function header()
    {
        return $this->belongsTo(Header::class, 'IdEncabezado');
    }
}
