<?php

namespace App\Models;

use App\Enums\ModePaiement;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'vente_id',
        'mode',
        'montant',
        'reference',
        'date',
        'compte_financier_id',
    ];

    protected function casts(): array
    {
        return [
            'mode' => ModePaiement::class,
            'montant' => 'decimal:2',
            'date' => 'datetime',
        ];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }
}
