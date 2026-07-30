<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'categorie_id',
        'nom',
        'reference',
        'code_barre',
        'marque',
        'unite_base',
        'prix_achat',
        'prix_vente',
        'stock_min',
        'photo',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix_achat' => 'decimal:2',
            'prix_vente' => 'decimal:2',
            'stock_min' => 'integer',
            'actif' => 'boolean',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function conditionnements(): HasMany
    {
        return $this->hasMany(Conditionnement::class);
    }

    /**
     * Retourne le conditionnement par défaut (Bouteille) pour ce produit.
     */
    public function conditionnementParDefaut(): HasOne
    {
        return $this->hasOne(Conditionnement::class)->where('is_par_defaut', true);
    }

    public function detailVentes(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }

    public function detailApprovisionnements(): HasMany
    {
        return $this->hasMany(DetailApprovisionnement::class);
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function inventaireDetails(): HasMany
    {
        return $this->hasMany(InventaireDetail::class);
    }

    public function deteriorationDetails(): HasMany
    {
        return $this->hasMany(DeteriorationDetail::class);
    }
}
