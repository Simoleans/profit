<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'art';
    protected $primaryKey = 'co_art';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'co_art',
        'art_des',
        'co_lin',
        'co_cat',
        'co_subl',
        'co_color',
        'modelo',
        'comentario',
        'uni_venta',
        'uni_compra',
        'uni_relac',
        'relac_aut',
        'stock_act',
        'stock_com',
        'stock_des',
        'prec_om',
        'prec_vta1',
        'prec_vta2',
        'prec_vta3',
        'prec_vta4',
        'prec_vta5'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'co_cat', 'co_cat');
    }


    public function line()
    {
        return $this->belongsTo(Line::class, 'co_lin', 'co_lin');
    }


    public function subl()
    {
        return $this->belongsTo(Subl::class, 'co_subl', 'co_subl');
    }

    /**
     * Scope para buscar por texto (código o descripción)
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('art_des', 'like', "%{$search}%")
              ->orWhere('co_art', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategory($query, $categoryId)
    {
        if (!$categoryId) {
            return $query;
        }

        return $query->where('co_cat', $categoryId);
    }

    /**
     * Scope para filtrar por línea
     */
    public function scopeByLine($query, $lineId)
    {
        if (!$lineId) {
            return $query;
        }

        return $query->where('co_lin', $lineId);
    }

    /**
     * Scope para filtrar por sublínea
     */
    public function scopeBySubl($query, $sublId)
    {
        if (!$sublId) {
            return $query;
        }

        return $query->where('co_subl', $sublId);
    }

    /**
     * Scope para filtrar por múltiples criterios
     */
    public function scopeFilter($query, $filters = [])
    {
        return $query->search($filters['search'] ?? null)
                    ->byCategory($filters['category'] ?? null)
                    ->byLine($filters['line'] ?? null)
                    ->bySubl($filters['subl'] ?? null);
    }
}
