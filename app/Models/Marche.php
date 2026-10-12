<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marche extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function articles()
    {
        return $this->hasMany(MarcheArticle::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
