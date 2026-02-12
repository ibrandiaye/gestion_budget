<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteParent extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'libelle', 'type'];

    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
