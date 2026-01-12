<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajustement extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'source_ligne_id',
        'dest_ligne_id',
        'montant',
        'date',
        'motif'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function sourceLigne()
    {
        return $this->belongsTo(LigneBudget::class, 'source_ligne_id');
    }

    public function destLigne()
    {
        return $this->belongsTo(LigneBudget::class, 'dest_ligne_id');
    }
}
