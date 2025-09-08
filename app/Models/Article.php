<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'dbo.art';   // exact SQL Server table
    protected $primaryKey = 'co_art'; // adjust to your real PK
    public $timestamps = false;     // disable timestamps if not present
}
