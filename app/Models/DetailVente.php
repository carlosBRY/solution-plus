<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailVente extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'detail_ventes';

    protected $fillable = [
        'vente_id',
        'produit_id',
        'conditionnement_id',
        'quantite',
        'quantite_conditionnement',
        'coefficient_conversion',
        'prix',
        'remise',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'quantite_conditionnement' => 'integer',
            'coefficient_conversion' => 'integer',
            'prix' => 'decimal:2',
            'remise' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function conditionnement(): BelongsTo
    {
        return $this->belongsTo(Conditionnement::class);
    }
}
