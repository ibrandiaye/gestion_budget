<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'libelle', 'type', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Compte::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Compte::class, 'parent_id');
    }

    public function lignesBudget()
    {
        return $this->hasMany(LigneBudget::class);
    }
}
