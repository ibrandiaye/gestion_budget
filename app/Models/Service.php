<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'code', 'chef_service', 'departement'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }

    public function lignesBudget()
    {
        return $this->hasMany(LigneBudget::class);
    }
}
