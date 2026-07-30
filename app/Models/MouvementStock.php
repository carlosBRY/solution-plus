<?php

namespace App\Models;

use App\Enums\MouvementType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'produit_id',
        'user_id',
        'conditionnement_id',
        'type',
        'quantite',
        'quantite_conditionnement',
        'coefficient_conversion',
        'stock_avant',
        'stock_apres',
        'motif',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'type' => MouvementType::class,
            'quantite' => 'integer',
            'quantite_conditionnement' => 'integer',
            'coefficient_conversion' => 'integer',
            'stock_avant' => 'integer',
            'stock_apres' => 'integer',
        ];
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conditionnement(): BelongsTo
    {
        return $this->belongsTo(Conditionnement::class);
    }
}
