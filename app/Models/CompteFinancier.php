<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompteFinancier extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'comptes_financiers';

    protected $fillable = [
        'nom',
        'mode',
        'solde_initial',
        'solde_courant',
        'actif',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'solde_initial' => 'decimal:2',
            'solde_courant' => 'decimal:2',
            'actif' => 'boolean',
        ];
    }

    /**
     * Mouvements financiers liés à ce compte.
     */
    public function mouvementsCompte(): HasMany
    {
        return $this->hasMany(MouvementCompte::class);
    }

    /**
     * Scope : comptes actifs uniquement.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
