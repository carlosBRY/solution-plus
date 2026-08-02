<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Fournisseur extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'adresse',
        'ville',
        'pays',
        'observation',
    ];

    protected static function booted(): void
    {
        static::created(function (Fournisseur $fournisseur) {
            $conditionnements = Conditionnement::whereNotNull('prix_achat')
                ->where('prix_achat', '>', 0)
                ->get();

            foreach ($conditionnements as $cond) {
                DB::table('fournisseur_produit')->updateOrInsert(
                    [
                        'fournisseur_id' => $fournisseur->id,
                        'produit_id' => $cond->produit_id,
                        'conditionnement_id' => $cond->id,
                    ],
                    [
                        'prix_achat' => (float) $cond->prix_achat,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    public function approvisionnements(): HasMany
    {
        return $this->hasMany(Approvisionnement::class);
    }

    public function tarifs()
    {
        return $this->belongsToMany(Conditionnement::class, 'fournisseur_produit')
            ->withPivot('produit_id', 'prix_achat')
            ->withTimestamps();
    }
}
