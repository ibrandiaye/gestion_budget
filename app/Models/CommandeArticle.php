<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'article_id',
        'designation',
        'conditionnement',
        'quantite',
        'prix_unitaire',
        'montant_ht',
        'valide',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'valide' => 'boolean',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
