<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PvReception extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_reception' => 'date',
        'signataires' => 'array',
        'pieces_justificatives' => 'array',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function articles()
    {
        return $this->hasMany(PvReceptionArticle::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signatairesRelation()
    {
        return $this->belongsToMany(Signataire::class, 'pv_reception_signataire')->orderBy('pv_reception_signataire.id');
    }
}
