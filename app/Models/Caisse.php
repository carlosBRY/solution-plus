<?php

namespace App\Models;

use App\Enums\StatutCaisse;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Caisse extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'date_ouverture',
        'date_fermeture',
        'solde_initial',
        'solde_final',
        'ecart',
        'statut',
        'details_comptes',
    ];

    protected function casts(): array
    {
        return [
            'date_ouverture' => 'datetime',
            'date_fermeture' => 'datetime',
            'solde_initial' => 'decimal:2',
            'solde_final' => 'decimal:2',
            'ecart' => 'decimal:2',
            'statut' => StatutCaisse::class,
            'details_comptes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
