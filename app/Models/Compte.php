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

    public function dotations()
    {
        return $this->hasMany(DotationCompte::class);
    }

    public function getDotationForBudget($budgetId)
    {
         $dotation = $this->dotations()->where('budget_id', $budgetId)->first();
         return $dotation ? $dotation->dotation_initiale : 0;
    }

    public function getPrixUnitaireRefForBudget($budgetId)
    {
         $dotation = $this->dotations()->where('budget_id', $budgetId)->first();
         return $dotation ? $dotation->prix_unitaire_ref : ($this->prix_unitaire_ref ?? 0); // Fallback to old field if not found, for safety
    }
}
