<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'commande_id',
        'date',
        'objet',
        'nature',
        'montant',
        'nom_fournisseur',
        'sonafel',
        'credits_ouverts',
        'modifications_credits',
        'engagements_anterieurs',
        'disponible_avant',
        'depenses_jour',
        'nouveau_disponible',
        'statut',
        'valide_par',
        'valide_at',
        'motif_rejet',
    ];

    protected $casts = [
        'date' => 'date',
        'valide_at' => 'datetime',
        'montant' => 'decimal:2',
        'credits_ouverts' => 'decimal:2',
        'modifications_credits' => 'decimal:2',
        'engagements_anterieurs' => 'decimal:2',
        'disponible_avant' => 'decimal:2',
        'depenses_jour' => 'decimal:2',
        'nouveau_disponible' => 'decimal:2',
    ];

    /**
     * Generate unique engagement number
     */
    public static function generateNumero()
    {
        $year = date('y');
        $prefix = 'BE' . $year . '-';
        $last = self::where('numero', 'like', $prefix . '%')
            ->orderByDesc('numero')
            ->first();

        if ($last) {
            $lastNum = intval(substr($last->numero, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $newNum;
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function mandat()
    {
        return $this->hasOne(Mandat::class);
    }
}
