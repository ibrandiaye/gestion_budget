<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneBudget extends Model
{
    use HasFactory;

    protected $table = 'lignes_budget';

    protected $fillable = [
        'budget_id',
        'compte_id',
        'service_id',
        'montant_alloue',
        'montant_engage',
        'montant_paye'
    ];

    protected $appends = ['solde_disponible'];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function executions()
    {
        return $this->hasMany(Execution::class, 'ligne_budget_id');
    }

    public function getSoldeDisponibleAttribute()
    {
        return $this->montant_alloue - $this->montant_engage;
    }
}
