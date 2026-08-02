<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeCasier extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'type_casiers';

    protected $fillable = [
        'nom',
        'capacite_bouteilles',
        'quantite_casiers_cave',
        'quantite_bouteilles_seules_cave',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'capacite_bouteilles' => 'integer',
            'quantite_casiers_cave' => 'integer',
            'quantite_bouteilles_seules_cave' => 'integer',
        ];
    }

    public function consignations(): HasMany
    {
        return $this->hasMany(ConsignationCasier::class, 'type_casier_id');
    }

    /**
     * Calcule le nombre total d'équivalents bouteilles en stock cave pour ce type de casier.
     */
    public function getTotalBouteillesCaveAttribute(): int
    {
        return ($this->quantite_casiers_cave * $this->capacite_bouteilles) + $this->quantite_bouteilles_seules_cave;
    }
}
