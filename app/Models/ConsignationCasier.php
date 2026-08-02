<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignationCasier extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'consignations_casiers';

    protected $fillable = [
        'type_casier_id',
        'client_id',
        'nom_personne',
        'contact_personne',
        'type_mouvement',
        'nombre_casiers',
        'nombre_bouteilles_seules',
        'statut',
        'date_mouvement',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'nombre_casiers' => 'integer',
            'nombre_bouteilles_seules' => 'integer',
            'date_mouvement' => 'datetime',
        ];
    }

    public function typeCasier(): BelongsTo
    {
        return $this->belongsTo(TypeCasier::class, 'type_casier_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retourne le nom d'affichage du bénéficiaire/déposant (Client enregistré ou Nom saisi).
     */
    public function getNomAffichageAttribute(): string
    {
        if ($this->client) {
            return $this->client->nom.' '.$this->client->prenom;
        }

        return $this->nom_personne ?: 'Client Anonyme / Passant';
    }

    /**
     * Retourne le contact d'affichage du bénéficiaire/déposant.
     */
    public function getContactAffichageAttribute(): string
    {
        if ($this->client) {
            return $this->client->telephone ?: '—';
        }

        return $this->contact_personne ?: '—';
    }
}
