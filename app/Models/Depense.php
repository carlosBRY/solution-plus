<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'libelle',
        'reference_piece',
        'categorie',
        'montant',
        'date',
        'observation',
        'mode',
        'compte_financier_id',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function compteFinancier(): BelongsTo
    {
        return $this->belongsTo(CompteFinancier::class);
    }
}
