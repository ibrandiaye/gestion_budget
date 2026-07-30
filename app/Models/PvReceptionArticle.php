<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PvReceptionArticle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pvReception()
    {
        return $this->belongsTo(PvReception::class);
    }

    public function commandeArticle()
    {
        return $this->belongsTo(CommandeArticle::class);
    }
}
