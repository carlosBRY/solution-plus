<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Fiche Client & Crédits</p>
                <h1 class="h3 mb-0">{{ $client->nom }}</h1>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('clients.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#ajouterCreditModal">
                    <i class="bi bi-plus-circle me-1"></i> Ajouter un Crédit
                </button>
                @if($isAdmin)
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#ajusterCreditModal">
                        <i class="bi bi-pencil-square me-1"></i> Ajuster le Solde
                    </button>
                @endif
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
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ $reg->compteFinancier?->nom ?? $reg->mode }}
                                        </span>
                                    </td>
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

            {{-- Historique des Ajustements de Crédit --}}
            <section class="panel mb-3">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-journal-arrow-up me-2"></i>Historique des Ajustements de Crédit</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Solde Avant</th>
                                <th>Solde Après</th>
                                <th>Motif</th>
                                <th>Par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($client->ajustementsCredit as $aj)
                                <tr>
                                    <td>{{ $aj->date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($aj->type === 'AJOUT')
                                            <span class="badge bg-warning text-dark">AJOUT</span>
                                        @else
                                            <span class="badge bg-danger">AJUSTEMENT</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold {{ $aj->montant >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $aj->montant >= 0 ? '+' : '' }}{{ number_format($aj->montant, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>{{ number_format($aj->solde_avant, 0, ',', ' ') }} FCFA</td>
                                    <td class="fw-bold">{{ number_format($aj->solde_apres, 0, ',', ' ') }} FCFA</td>
                                    <td class="small">{{ Str::limit($aj->motif, 60) }}</td>
                                    <td>{{ $aj->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Aucun ajustement de crédit enregistré.</td>
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
                                        @if($v->statut->value === 'PAYEE_CREDIT' || $v->statut === \App\Enums\StatutVente::PAYEE_CREDIT)
                                            <span class="badge bg-info text-dark">CRÉDIT RÉGLÉ</span>
                                            @if($v->date_paiement_credit)
                                                <small class="d-block text-muted fs-7">le {{ $v->date_paiement_credit->format('d/m/Y') }}</small>
                                            @endif
                                        @elseif($v->statut->value === 'PAYEE' || $v->statut === \App\Enums\StatutVente::PAYEE)
                                            <span class="badge bg-success">PAYÉE (COMPTANT)</span>
                                        @else
                                            <span class="badge bg-warning text-dark">CRÉDIT EN ATTENTE</span>
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
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('clients.regler-dette', $client) }}">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Règlement de Dette - {{ $client->nom }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Dette actuelle du client à rembourser : <strong>{{ number_format($client->solde, 0, ',', ' ') }} FCFA</strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Montant Encaissé (FCFA) <span class="text-danger">*</span></label>
                                <input class="form-control fw-bold fs-5 text-success" type="number" step="0.01" min="1" max="{{ $client->solde }}" name="montant" value="{{ $client->solde }}" required>
                                <small class="text-muted">Somme versée par le client pour rembourser sa dette.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Compte / Caisses Réceptrice (Entrée d'argent) <span class="text-danger">*</span></label>
                                <select class="form-select fw-semibold" name="compte_financier_id" required>
                                    @foreach($comptes as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->nom }} (Solde actuel: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-success fw-semibold"><i class="bi bi-arrow-down-circle me-1"></i>L'argent versé sera immédiatement <u>crédité</u> (ajouté) sur ce compte.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Référence Transaction / Pièce</label>
                                <input class="form-control" type="text" name="reference" placeholder="ex: N° Reçu, Réf transaction Wave / Orange Money...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observation</label>
                                <textarea class="form-control" name="observation" rows="2" placeholder="Note optionnelle..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success fw-bold" data-confirm="Confirmer le règlement de dette de ce client ?"><i class="bi bi-check-circle me-1"></i> Encaisser le Remboursement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Ajouter un Crédit --}}
    <div class="modal fade" id="ajouterCreditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('clients.ajouter-credit', $client) }}">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Ajouter un Crédit - {{ $client->nom }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Cette opération ajoute une dette au client <strong>sans passer par une vente</strong>.
                            Le solde actuel est de <strong>{{ number_format($client->solde, 0, ',', ' ') }} FCFA</strong>.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Montant du Crédit (FCFA) <span class="text-danger">*</span></label>
                            <input class="form-control fw-bold fs-5" type="number" step="0.01" min="1" name="montant" required placeholder="Ex: 50000">
                            <small class="text-muted">Montant de la dette à ajouter au solde du client.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Motif / Raison <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="motif" rows="3" required placeholder="Ex: Prêt accordé, avance sur marchandise, dette contractée hors vente..."></textarea>
                            <small class="text-muted">Le motif est obligatoire pour la traçabilité.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning fw-bold" data-confirm="Confirmer l'ajout de ce crédit ?"><i class="bi bi-plus-circle me-1"></i> Ajouter le Crédit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Ajuster le Solde (Admin uniquement) --}}
    @if($isAdmin)
        <div class="modal fade" id="ajusterCreditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('clients.ajuster-credit', $client) }}">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Ajuster le Solde - {{ $client->nom }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-danger small mb-3">
                                <i class="bi bi-shield-lock me-1"></i>
                                <strong>Action Administrateur :</strong> cette opération remplace le solde du client par la valeur saisie.
                                Utilisez-la uniquement pour corriger une erreur.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Solde Actuel</label>
                                <input class="form-control bg-light" type="text" value="{{ number_format($client->solde, 0, ',', ' ') }} FCFA" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nouveau Solde (FCFA) <span class="text-danger">*</span></label>
                                <input class="form-control fw-bold fs-5 text-danger" type="number" step="0.01" min="0" name="nouveau_solde" required value="{{ $client->solde }}">
                                <small class="text-muted">Le solde du client sera remplacé par cette valeur. Mettre 0 pour annuler toute la dette.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Motif de la Correction <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="motif" rows="3" required placeholder="Ex: Erreur de saisie lors de la vente #XXX, double comptabilisation..."></textarea>
                                <small class="text-muted">Le motif est obligatoire pour la traçabilité et l'audit.</small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger fw-bold" data-confirm="Êtes-vous sûr de vouloir ajuster le solde de ce client ? Cette action est irréversible."><i class="bi bi-pencil-square me-1"></i> Ajuster le Solde</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</x-app-layout>
