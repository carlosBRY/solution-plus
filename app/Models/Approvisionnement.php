<?php

namespace App\Models;

use App\Enums\StatutApprovisionnement;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Approvisionnement extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'fournisseur_id',
        'user_id',
        'numero',
        'reference_facture',
        'date',
        'montant',
        'remise',
        'tva',
        'total',
        'statut',
        'mode',
        'compte_financier_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'montant' => 'decimal:2',
            'remise' => 'decimal:2',
            'tva' => 'decimal:2',
            'total' => 'decimal:2',
            'statut' => StatutApprovisionnement::class,
        ];
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function compteFinancier(): BelongsTo
    {
        return $this->belongsTo(CompteFinancier::class);
    }

    public function detailApprovisionnements(): HasMany
    {
        return $this->hasMany(DetailApprovisionnement::class);
    }

    public function details(): HasMany
    {
        return $this->detailApprovisionnements();
    }
}
