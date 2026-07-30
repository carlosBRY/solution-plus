<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Fiche Client & Crédits</p>
                <h1 class="h3 mb-0">{{ $client->nom }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('clients.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
                @if($client->solde > 0)
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#reglerDetteModal">
                        <i class="bi bi-cash-stack me-1"></i> Régler / Rembourser la Dette
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="panel mb-3">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-person-badge me-2"></i>Profil & Coordonnées</h2>
                <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Nom Complexe</span>
                        <span class="fw-bold">{{ $client->nom }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Téléphone</span>
                        <span>{{ $client->telephone ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">E-mail</span>
                        <span>{{ $client->email ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Adresse</span>
                        <span>{{ $client->adresse ?? '—' }}</span>
                    </li>
                </ul>

                <h2 class="h5 mb-3 section-title"><i class="bi bi-credit-card-2-front me-2"></i>Situation Financière</h2>
                <div class="p-3 rounded border mb-3 {{ $client->solde > 0 ? 'bg-danger-subtle border-danger-subtle' : 'bg-success-subtle border-success-subtle' }}">
                    <span class="small text-muted fw-semibold d-block">Dette / Crédit Actuel en Cours</span>
                    <h3 class="fw-bold mb-0 mt-1 {{ $client->solde > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($client->solde, 0, ',', ' ') }} FCFA
                    </h3>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Plafond Crédit Autorisé</span>
                        <span class="fw-semibold">{{ $client->plafond_credit > 0 ? number_format($client->plafond_credit, 0, ',', ' ') . ' FCFA' : 'Illimité' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Total Cumulé des Achats</span>
                        <span class="fw-bold text-primary">{{ number_format($totalAchats, 0, ',', ' ') }} FCFA</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            {{-- Historique des Remboursements de Dette --}}
            <section class="panel mb-3">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-journal-check me-2"></i>Historique des Remboursements de Dette</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Reçu</th>
                                <th>Date</th>
                                <th>Montant Remboursé</th>
                                <th>Mode Règlement</th>
                                <th>Reçu par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($client->reglementsDettes as $reg)
                                <tr>
                                    <td class="fw-bold">{{ $reg->numero }}</td>
                                    <td>{{ $reg->date->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold text-success">{{ number_format($reg->montant, 0, ',', ' ') }} FCFA</td>
                                    <td><span class="badge bg-light text-body border">{{ $reg->mode }}</span></td>
                                    <td>{{ $reg->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Aucun remboursement de dette enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Historique des Achats / Ventes --}}
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-cart-check me-2"></i>Dernières Ventes Rattachées</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Ticket</th>
                                <th>Date</th>
                                <th>Total Vente</th>
                                <th>Montant Payé</th>
                                <th>Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($client->ventes as $v)
                                <tr>
                                    <td><a href="{{ route('ventes.show', $v) }}" class="fw-bold text-decoration-none">{{ $v->numero }}</a></td>
                                    <td>{{ $v->date->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold">{{ number_format($v->total, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ number_format($v->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @if($v->statut->value === 'PAYEE' || $v->statut === \App\Enums\StatutVente::PAYEE)
                                            <span class="badge bg-success">PAYÉE</span>
                                        @else
                                            <span class="badge bg-warning text-dark">CRÉDIT / EN ATTENTE</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('ventes.show', $v) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Aucune vente effectuée pour ce client.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Modal Remboursement Dette --}}
    @if($client->solde > 0)
        <div class="modal fade" id="reglerDetteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('clients.regler-dette', $client) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Règlement de Dette - {{ $client->nom }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info small mb-3">
                                Dette actuelle restant à régler : <strong>{{ number_format($client->solde, 0, ',', ' ') }} FCFA</strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Montant du Règlement (FCFA) <span class="text-danger">*</span></label>
                                <input class="form-control fw-bold fs-5 text-success" type="number" step="0.01" name="montant" max="{{ $client->solde }}" value="{{ $client->solde }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mode de Règlement <span class="text-danger">*</span></label>
                                <select class="form-select" name="mode" required>
                                    <option value="ESPECES">Espèces</option>
                                    <option value="ORANGE_MONEY">Orange Money</option>
                                    <option value="MOOV_MONEY">Moov Money / Wave</option>
                                    <option value="CARTE">Carte Bancaire</option>
                                    <option value="VIREMENT">Virement Bancaire</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Référence Transaction</label>
                                <input class="form-control" type="text" name="reference" placeholder="ex: Réf transaction Wave / Chèque N°">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observation</label>
                                <textarea class="form-control" name="observation" rows="2" placeholder="Note optionnelle..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Enregistrer le Remboursement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
