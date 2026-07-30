<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetourDetail extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'retour_details';

    protected $fillable = [
        'retour_id',
        'produit_id',
        'quantite',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
        ];
    }

    public function retourVente(): BelongsTo
    {
        return $this->belongsTo(RetourVente::class, 'retour_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
