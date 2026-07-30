<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conditionnement extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'produit_id',
        'nom',
        'quantite_unite_base',
        'prix_achat',
        'prix_vente',
        'code_barre',
        'is_achat',
        'is_vente',
        'is_par_defaut',
    ];

    protected function casts(): array
    {
        return [
            'quantite_unite_base' => 'integer',
            'prix_achat' => 'decimal:2',
            'prix_vente' => 'decimal:2',
            'is_achat' => 'boolean',
            'is_vente' => 'boolean',
            'is_par_defaut' => 'boolean',
        ];
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function detailApprovisionnements(): HasMany
    {
        return $this->hasMany(DetailApprovisionnement::class);
    }

    public function detailVentes(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }

    public function deteriorationDetails(): HasMany
    {
        return $this->hasMany(DeteriorationDetail::class);
    }
}
