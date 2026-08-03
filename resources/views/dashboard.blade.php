<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <p class="eyebrow mb-1 text-muted">Vue d'ensemble commerciale</p>
                <h1 class="h3 mb-0">Tableau de bord - {{ $parametre->nom_cave }}</h1>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <button class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i> Exporter Rapport</button>
                <a class="btn btn-primary btn-sm" href="{{ route('ventes.create') }}"><i class="bi bi-cart-plus me-1"></i> Nouvelle Vente</a>
            </div>
        </div>
    </x-slot>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Chiffre d'Affaires</span>
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-currency-dollar fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalVentes, 0, ',', ' ') }} FCFA</h3>
                <span class="text-muted small"><i class="bi bi-graph-up-arrow text-success me-1"></i>Ventes cumulées</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Stock en Cave</span>
                    <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="bi bi-box-seam fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalBouteillesStock, 0, ',', ' ') }}</h3>
                <span class="text-muted small">Bouteilles disponibles</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Alerte Stock Bas</span>
                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="bi bi-exclamation-triangle fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-1">{{ $produitsAlerte->count() }}</h3>
                <span class="text-muted small">Produits sous le seuil min</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Statut Caisse</span>
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-cash-stack fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-1">
                    @if($caisseOuverte)
                        <span class="text-success fs-5"><i class="bi bi-check-circle-fill me-1"></i>Ouverte</span>
                    @else
                        <span class="text-secondary fs-5"><i class="bi bi-dash-circle-fill me-1"></i>Fermée</span>
                    @endif
                </h3>
                <span class="text-muted small">{{ $caisseOuverte ? 'Ouverte à ' . $caisseOuverte->date_ouverture->format('H:i') : 'Aucune caisse active' }}</span>
            </div>
        </div>
    </div>

    {{-- Carte Unique de Synthèse Financière & Trésorerie --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="bi bi-wallet2 text-primary me-2"></i>Synthèse Financière & Trésorerie
            </h5>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-clock-history me-1"></i>Mis à jour en temps réel
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                {{-- 1. Soldes des Comptes & Caisse Principale --}}
                <div class="col-12 col-md-6 col-xl-3 border-end-md d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-primary-subtle text-primary p-2 rounded me-2">
                            <i class="bi bi-bank fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Soldes des Comptes</h6>
                            <small class="text-muted">Caisse principale & comptes</small>
                        </div>
                    </div>
                    <div class="pe-xl-2 flex-grow-1 d-flex flex-column justify-content-between">
                        <div>
                            @forelse($comptesFinanciers as $compte)
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom-dashed">
                                    <span class="small fw-semibold text-secondary">
                                        <i class="bi bi-credit-card-2-front me-1"></i>{{ $compte->nom }}
                                    </span>
                                    <span class="fw-bold text-dark small">
                                        {{ number_format($compte->solde_courant, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            @empty
                                <span class="text-muted small">Aucun compte financier configuré.</span>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top fw-bold">
                            <span class="small text-primary">TOTAL TRÉSORERIE</span>
                            <span class="text-primary">
                                {{ number_format($comptesFinanciers->sum('solde_courant'), 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 2. Ventes (Jour / Semaine / Mois) --}}
                <div class="col-12 col-md-6 col-xl-3 border-end-xl d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-success-subtle text-success p-2 rounded me-2">
                            <i class="bi bi-cart-check fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Ventes de la Période</h6>
                            <small class="text-muted">Ventes réalisées</small>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2 flex-grow-1 justify-content-between">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <span class="small text-muted fw-semibold">Aujourd'hui :</span>
                            <span class="fw-bold text-success">{{ number_format($ventesJour, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <span class="small text-muted fw-semibold">Cette semaine :</span>
                            <span class="fw-bold text-success">{{ number_format($ventesSemaine, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <span class="small text-muted fw-semibold">Ce mois :</span>
                            <span class="fw-bold text-success">{{ number_format($ventesMois, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Dépenses (Jour / Mois) --}}
                <div class="col-12 col-md-6 col-xl-3 border-end-md d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-danger-subtle text-danger p-2 rounded me-2">
                            <i class="bi bi-arrow-down-right-circle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Dépenses & Charges</h6>
                            <small class="text-muted">Sorties de caisse</small>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2 flex-grow-1 justify-content-between">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <span class="small text-muted fw-semibold">Aujourd'hui :</span>
                            <span class="fw-bold text-danger">{{ number_format($depensesJour, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                            <span class="small text-muted fw-semibold">Ce mois :</span>
                            <span class="fw-bold text-danger">{{ number_format($depensesMois, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                {{-- 4. Crédits Octroyés --}}
                <div class="col-12 col-md-6 col-xl-3 d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-warning-subtle text-warning p-2 rounded me-2">
                            <i class="bi bi-person-exclamation fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Crédits Octroyés</h6>
                            <small class="text-muted">Encours créances clients</small>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25 flex-grow-1 d-flex flex-column justify-content-center">
                        <span class="small text-muted fw-semibold mb-1">Total Dettes Clients en cours :</span>
                        <h4 class="fw-bold text-warning-emphasis mb-0">
                            {{ number_format($totalCreditsOctroyes, 0, ',', ' ') }} FCFA
                        </h4>
                        <small class="text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i>Somme à recouvrir auprès des clients
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Dernières Ventes -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Dernières Ventes au Comptoir</h5>
                    <a href="#" class="btn btn-link btn-sm text-decoration-none">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-paginated align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Vente</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dernieresVentes as $vente)
                                <tr>
                                    <td><span class="fw-semibold text-primary">{{ $vente->numero }}</span></td>
                                    <td>{{ $vente->client?->nom ?? 'Client de passage' }}</td>
                                    <td>
                                        @if($vente->statut->value === 'PAYEE')
                                            <span class="badge bg-success-subtle text-success">Payée</span>
                                        @elseif($vente->statut->value === 'EN_ATTENTE')
                                            <span class="badge bg-warning-subtle text-warning">En attente</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Annulée</span>
                                        @endif
                                    </td>
                                    <td>{{ $vente->date->format('d/m/Y H:i') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aucune vente enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Alertes de Stock -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold mb-0 text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>Produits en Réapprovisionnement</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($produitsAlerte as $produit)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $produit->nom }}</h6>
                                    <small class="text-muted">{{ $produit->categorie->nom }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger rounded-pill fw-bold">{{ $produit->stock_formate }}</span>
                                    <div class="small text-muted">Min: {{ $produit->stock_min }} bts</div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">Aucune alerte de stock.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
