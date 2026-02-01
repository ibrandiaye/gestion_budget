<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'telephone',
        'telephone2',
        'email',
        'adresse',
        'ninea',
        'registre_commerce',
        'banque',
        'numero_compte',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Articles fournis par ce fournisseur avec leurs prix
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'fournisseur_article_prix')
            ->withPivot('prix')
            ->withTimestamps();
    }

    /**
     * Articles avec prix pour un compte spécifique
     */
    public function articlesPourCompte($compteId)
    {
        return $this->articles()->where('compte_id', $compteId)->get();
    }

    /**
     * Scope actif
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
