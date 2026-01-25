<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'besoin_id',
        'article_id',
        'quantite',
        'prix_unitaire',
        'montant',
        'fournisseur',
    ];

    public function execution()
    {
        return $this->belongsTo(Execution::class);
    }

    public function besoin()
    {
        return $this->belongsTo(Besoin::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
