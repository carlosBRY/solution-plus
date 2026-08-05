<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjustementCredit extends Model
{
    use HasUlids;

    protected $table = 'ajustements_credit';

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'montant',
        'solde_avant',
        'solde_apres',
        'motif',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'solde_avant' => 'decimal:2',
            'solde_apres' => 'decimal:2',
            'date' => 'datetime',
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
}
