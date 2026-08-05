<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'adresse',
        'solde',
        'plafond_credit',
    ];

    protected function casts(): array
    {
        return [
            'solde' => 'decimal:2',
            'plafond_credit' => 'decimal:2',
        ];
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    public function reglementsDettes(): HasMany
    {
        return $this->hasMany(ReglementDette::class);
    }

    public function ajustementsCredit(): HasMany
    {
        return $this->hasMany(AjustementCredit::class);
    }

    /**
     * Vérifie si l'ajout d'une nouvelle dette dépasserait le plafond de crédit configuré.
     */
    public function depassePlafondCredit(float $nouveauMontantDette): bool
    {
        if ($this->plafond_credit <= 0) {
            return false; // Pas de plafond spécifique imposé
        }

        return ($this->solde + $nouveauMontantDette) > $this->plafond_credit;
    }
}
