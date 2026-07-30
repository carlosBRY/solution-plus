<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between ">
            <div style="margin-right: 100px;">
                <p class="eyebrow mb-1 text-muted">Vue d'ensemble commerciale</p>
                <h1 class="h3 mb-0">Tableau de bord - {{ $parametre->nom_cave }}</h1>
            </div>
            <div class="d-flex gap-2 align-items-baseline">
                <button class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i> Exporter Rapport</button>
                <button class="btn btn-primary btn-sm"><i class="bi bi-cart-plus me-1"></i> Nouvelle Vente</button>
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

    <div class="row g-4">
        <!-- Dernières Ventes -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Dernières Ventes au Comptoir</h5>
                    <a href="#" class="btn btn-link btn-sm text-decoration-none">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                    <span class="badge bg-danger rounded-pill">{{ $produit->stock?->quantite ?? 0 }} en stock</span>
                                    <div class="small text-muted">Min: {{ $produit->stock_min }}</div>
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
