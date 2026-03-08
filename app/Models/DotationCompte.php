<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DotationCompte extends Model
{

    protected $table = 'dotations_comptes';

    protected $fillable = ['budget_id', 'compte_id', 'dotation_initiale', 'prix_unitaire_ref'];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
