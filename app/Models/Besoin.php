<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Besoin extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'budget_id',
        'compte_id',
        'montant_demande',
        'montant_accorde',
        'statut',
        'justification',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
