<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'visibility'
    ];

    // Mutator para limpiar espacios del mediable_id al guardar
    public function setMediableIdAttribute($value)
    {
        $this->attributes['mediable_id'] = trim($value);
    }

    public function mediable()
    {
        return $this->morphTo();
    }
}
