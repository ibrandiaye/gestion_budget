<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'date',
        'compte_id',
        'service_id',
        'budget_id',
        'fournisseur',
        'reference',
        'caisse',
        'montant_tva',
        'montant_ttc',
        'arrete_somme',
        'statut',
        'valide_par',
        'valide_at',
        'motif_rejet',
        'fichier',
    ];

    protected $casts = [
        'date' => 'date',
        'valide_at' => 'datetime',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    /**
     * Generate unique command number
     */
    public static function generateNumero()
    {
        $year = date('y');
        $prefix = 'CMD' . $year . '-';
        $lastCommande = self::where('numero', 'like', $prefix . '%')
            ->orderByDesc('numero')
            ->first();

        if ($lastCommande) {
            $lastNum = intval(substr($lastCommande->numero, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $newNum;
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function articles()
    {
        return $this->hasMany(CommandeArticle::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function engagement()
    {
        return $this->hasOne(Engagement::class);
    }

    public function pvReceptions()
    {
        return $this->hasMany(PvReception::class);
    }

    public function articlesValides()
    {
        return $this->articles()->where('valide', true);
    }

    public function getMontantValidesAttribute()
    {
        return $this->articlesValides()->sum('montant_ht');
    }
}
