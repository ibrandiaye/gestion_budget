<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = ['annee', 'statut', 'date_debut', 'date_fin'];

    public function lignesBudget()
    {
        return $this->hasMany(LigneBudget::class);
    }

    public function ajustements()
    {
        return $this->hasMany(Ajustement::class);
    }

    public function dotationsComptes()
    {
        return $this->hasMany(DotationCompte::class);
    }
}
