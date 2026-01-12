<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    use HasFactory;

    protected $fillable = [
        'ligne_budget_id',
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
}
