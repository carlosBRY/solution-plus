<?php

namespace App\Models;

use App\Enums\StatutVente;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vente extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'client_id',
        'client_comptant_nom',
        'client_comptant_prenom',
        'client_comptant_contact',
        'user_id',
        'numero',
        'date',
        'sous_total',
        'remise',
        'tva',
        'total',
        'montant_paye',
        'monnaie',
        'is_credit',
        'date_paiement_credit',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'date_paiement_credit' => 'datetime',
            'sous_total' => 'decimal:2',
            'remise' => 'decimal:2',
            'tva' => 'decimal:2',
            'total' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'monnaie' => 'decimal:2',
            'is_credit' => 'boolean',
            'statut' => StatutVente::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailVentes(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }

    public function details(): HasMany
    {
        return $this->detailVentes();
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function retoursVentes(): HasMany
    {
        return $this->hasMany(RetourVente::class);
    }

    /**
     * Montant net effectivement encaissé en caisse pour cette vente (Montant Reçu - Monnaie Rendue).
     */
    public function getMontantEncaisseAttribute(): float
    {
        return max(0, (float) $this->montant_paye - (float) $this->monnaie);
    }
}
