<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'nom_cave',
        'telephone',
        'adresse',
        'email',
        'logo',
        'devise',
        'tva',
        'stock_min_global',
        'message_ticket',
    ];

    protected function casts(): array
    {
        return [
            'tva' => 'decimal:2',
            'stock_min_global' => 'integer',
        ];
    }
}
