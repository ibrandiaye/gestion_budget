<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'compte_id',
        'designation',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Get the compte that owns the article.
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    /**
     * Scope a query to only include active articles.
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Fournisseurs qui proposent cet article avec leurs prix
     */
    public function fournisseurs()
    {
        return $this->belongsToMany(Fournisseur::class, 'fournisseur_article_prix')
            ->withPivot('prix')
            ->withTimestamps();
    }
}
