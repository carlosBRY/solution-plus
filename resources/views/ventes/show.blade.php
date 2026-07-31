<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Reçu de Vente</p>
                <h1 class="h3 mb-0">Ticket {{ $vente->numero }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('ventes.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour aux Ventes
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer le Reçu
                </button>
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

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-receipt me-2"></i>Détail du Ticket de Vente</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Conditionnement</th>
                                <th>Qté Cond.</th>
                                <th>Équivalent Stock</th>
                                <th>Prix Unit.</th>
                                <th>Remise</th>
                                <th>Total Ligne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vente->details as $detail)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $detail->produit->nom }}</div>
                                        <small class="text-muted">{{ $detail->produit->unite_base }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-body border">{{ $detail->conditionnement?->nom ?? '—' }}</span></td>
                                    <td class="fw-bold">{{ $detail->quantite_conditionnement ?? $detail->quantite }}</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger">
                                            -{{ $detail->quantite }} {{ $detail->produit->unite_base }}(s)
                                        </span>
                                    </td>
                                    <td>{{ number_format($detail->prix, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $detail->remise > 0 ? number_format($detail->remise, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                                    <td class="fw-bold text-success">{{ number_format($detail->total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Sous-total :</td>
                                <td class="fw-bold">{{ number_format($vente->sous_total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @if($vente->remise > 0)
                                <tr>
                                    <td colspan="6" class="text-end text-muted">Remise globale :</td>
                                    <td class="text-danger">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                            @if($vente->tva > 0)
                                <tr>
                                    <td colspan="6" class="text-end text-muted">TVA :</td>
                                    <td>+{{ number_format($vente->tva, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                            <tr class="table-success">
                                <td colspan="6" class="text-end fw-bold fs-5">Total à Payer :</td>
                                <td class="fw-bold text-success fs-5">{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end fw-semibold">Montant Reçu :</td>
                                <td class="fw-bold">{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @if($vente->monnaie > 0)
                                <tr>
                                    <td colspan="6" class="text-end fw-semibold">Monnaie Rendue :</td>
                                    <td class="fw-bold text-primary">{{ number_format($vente->monnaie, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel mb-3">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-info-circle me-2"></i>Détails de la Transaction</h2>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">N° Ticket</span>
                        <span class="fw-bold">{{ $vente->numero }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Client</span>
                        <span>{{ $vente->client ? $vente->client->nom . ' ' . $vente->client->prenom : 'Client Comptant' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Vendeur</span>
                        <span>{{ $vente->user->name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Date</span>
                        <span>{{ $vente->date->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Statut</span>
                        @if($vente->statut->value === 'PAYEE_CREDIT' || $vente->statut === \App\Enums\StatutVente::PAYEE_CREDIT)
                            <span class="badge bg-info text-dark">CRÉDIT RÉGLÉ</span>
                        @elseif($vente->statut->value === 'PAYEE' || $vente->statut === \App\Enums\StatutVente::PAYEE)
                            <span class="badge bg-success">PAYÉE (COMPTANT)</span>
                        @elseif($vente->statut->value === 'EN_ATTENTE' || $vente->statut === \App\Enums\StatutVente::EN_ATTENTE)
                            <span class="badge bg-warning text-dark">CRÉDIT EN ATTENTE</span>
                        @else
                            <span class="badge bg-secondary">ANNULÉE</span>
                        @endif
                    </li>
                    @if($vente->date_paiement_credit)
                        <li class="list-group-item d-flex justify-content-between bg-info-subtle">
                            <span class="fw-semibold text-dark"><i class="bi bi-calendar-check me-1"></i>Date Règlement Crédit</span>
                            <span class="fw-bold text-dark">{{ $vente->date_paiement_credit->format('d/m/Y H:i') }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="panel">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-cash-coin me-2"></i>Règlement(s)</h2>
                @forelse($vente->paiements as $paiement)
                    <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-light-subtle">
                        <div>
                            <span class="badge bg-primary-subtle text-primary me-1">{{ $paiement->mode }}</span>
                            <small class="text-muted">{{ $paiement->date->format('d/m/Y H:i') }}</small>
                        </div>
                        <span class="fw-bold text-success">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
                    </div>
                @empty
                    <p class="text-muted small">Aucun règlement enregistré.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
