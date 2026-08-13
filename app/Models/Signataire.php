<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signataire extends Model
{
    protected $fillable = ['prenom', 'nom', 'matricule', 'fonction', 'actif'];

    public function pvReceptions()
    {
        return $this->belongsToMany(PvReception::class, 'pv_reception_signataire');
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
