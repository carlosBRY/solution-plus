<?php

namespace App\Models;

use App\Enums\StatutDeterioration;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deterioration extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'numero',
        'date',
        'statut',
        'valide_par',
        'date_validation',
        'total_perte',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutDeterioration::class,
            'date' => 'datetime',
            'date_validation' => 'datetime',
            'total_perte' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeteriorationDetail::class);
    }

    /**
     * Vérifie si la détérioration est en brouillon.
     */
    public function isEstBrouillon(): bool
    {
        return $this->statut === StatutDeterioration::BROUILLON;
    }

    /**
     * Vérifie si la détérioration est validée.
     */
    public function isEstValidee(): bool
    {
        return $this->statut === StatutDeterioration::VALIDEE;
    }

    /**
     * Vérifie si la détérioration est modifiable.
     */
    public function isEstModifiable(): bool
    {
        return $this->isEstBrouillon();
    }
}
