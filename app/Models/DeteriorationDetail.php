<?php

namespace App\Models;

use App\Enums\DeteriorationCause;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeteriorationDetail extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'deterioration_id',
        'produit_id',
        'conditionnement_id',
        'quantite_conditionnement',
        'coefficient_conversion',
        'quantite_unite_base',
        'cout_unitaire',
        'valeur_perte',
        'cause',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'cause' => DeteriorationCause::class,
            'quantite_conditionnement' => 'integer',
            'coefficient_conversion' => 'integer',
            'quantite_unite_base' => 'integer',
            'cout_unitaire' => 'decimal:2',
            'valeur_perte' => 'decimal:2',
        ];
    }

    public function deterioration(): BelongsTo
    {
        return $this->belongsTo(Deterioration::class);
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
