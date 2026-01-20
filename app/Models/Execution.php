<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    use HasFactory;

    protected $fillable = [
        'ligne_budget_id',
        'besoin_id',
        'quantite',
        'montant',
        'date',
        'type',
        'description',
        'reference_document'
    ];

    public function ligneBudget()
    {
        return $this->belongsTo(LigneBudget::class);
    }

    public function details()
    {
        return $this->hasMany(ExecutionDetail::class);
    }

    // Keep for backward compatibility or individual item access
    public function besoin()
    {
        return $this->belongsTo(Besoin::class);
    }
}
