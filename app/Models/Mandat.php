<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mandat extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'engagement_id',
        'date',
        'objet_depense',
        'montant',
        'montant_lettres',
        'pieces_justificatives',
        'beneficiaire',
        'compte_principal',
        'compte_imputation',
        'libelle_imputation',
        'mode_reglement',
        'etablissement',
        'numero_compte',
        'rib',
        'statut',
        'valide_directeur_par',
        'valide_directeur_at',
        'valide_comptable_par',
        'valide_comptable_at',
        'motif_refus',
        'requisition',
    ];

    protected $casts = [
        'date' => 'date',
        'valide_directeur_at' => 'datetime',
        'valide_comptable_at' => 'datetime',
        'montant' => 'decimal:2',
        'requisition' => 'boolean',
    ];

    /**
     * Generate unique mandat number
     */
    public static function generateNumero()
    {
        $year = date('y');
        $prefix = 'MD' . $year . '-';
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

    public function engagement()
    {
        return $this->belongsTo(Engagement::class);
    }

    public function valideDirecteurPar()
    {
        return $this->belongsTo(User::class, 'valide_directeur_par');
    }

    public function valideComptablePar()
    {
        return $this->belongsTo(User::class, 'valide_comptable_par');
    }

    public function isPaye()
    {
        return in_array($this->statut, ['valide_comptable', 'requisition', 'paye']);
    }

    public function canBeValidatedByDirecteur()
    {
        return $this->statut === 'cree';
    }

    public function canBeValidatedByComptable()
    {
        return $this->statut === 'valide_directeur';
    }

    public function canBeRequisitioned()
    {
        return $this->statut === 'refuse_comptable';
    }
}
