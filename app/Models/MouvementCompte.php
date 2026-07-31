<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementCompte extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'mouvements_compte';

    protected $fillable = [
        'compte_financier_id',
        'user_id',
        'type',
        'montant',
        'solde_avant',
        'solde_apres',
        'motif',
        'reference_id',
        'reference_type',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'solde_avant' => 'decimal:2',
            'solde_apres' => 'decimal:2',
        ];
    }

    /**
     * Compte financier associé.
     */
    public function compteFinancier(): BelongsTo
    {
        return $this->belongsTo(CompteFinancier::class);
    }

    /**
     * Utilisateur ayant effectué le mouvement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
