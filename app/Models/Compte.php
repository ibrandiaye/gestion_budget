<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'libelle', 'type', 'is_marche', 'fournisseur_requis', 'dotation_initiale', 'prix_unitaire_ref', 'parent_id', 'etat'];

    protected $casts = [
        'is_marche' => 'boolean',
        'fournisseur_requis' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Compte::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Compte::class, 'parent_id');
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
