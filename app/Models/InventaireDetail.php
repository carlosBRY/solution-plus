<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventaireDetail extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'inventaire_details';

    protected $fillable = [
        'inventaire_id',
        'produit_id',
        'stock_theorique',
        'stock_physique',
        'ecart',
    ];

    protected function casts(): array
    {
        return [
            'stock_theorique' => 'integer',
            'stock_physique' => 'integer',
            'ecart' => 'integer',
        ];
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
