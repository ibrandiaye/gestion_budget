<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'libelle', 'compte_parent_id', 'is_marche', 'fournisseur_requis', 'dotation_initiale', 'prix_unitaire_ref', 'etat'];

    protected $casts = [
        'is_marche' => 'boolean',
        'fournisseur_requis' => 'boolean',
    ];

    public function compteParent()
    {
        return $this->belongsTo(CompteParent::class);
    }

    public function lignesBudget()
    {
        return $this->hasMany(LigneBudget::class);
    }

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
