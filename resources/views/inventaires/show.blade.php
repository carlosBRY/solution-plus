<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Inventaire & Logistique</p>
                <h1 class="h3 mb-0">Rapport d'Inventaire — {{ $inventaire->date->format('d/m/Y à H:i') }}</h1>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
                <a href="{{ route('inventaires.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Summary KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <div class="fw-bold fs-3 text-primary">{{ $inventaire->inventaireDetails->count() }}</div>
                    <p class="text-muted small mb-0">Produits Comptés</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <div class="fw-bold fs-3">{{ number_format($totalTheorique) }}</div>
                    <p class="text-muted small mb-0">Stock Théorique</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <div class="fw-bold fs-3">{{ number_format($totalPhysique) }}</div>
                    <p class="text-muted small mb-0">Stock Physique</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <div class="fw-bold fs-3 {{ $totalEcart < 0 ? 'text-danger' : ($totalEcart > 0 ? 'text-success' : 'text-muted') }}">
                        {{ $totalEcart >= 0 ? '+' : '' }}{{ number_format($totalEcart) }}
                    </div>
                    <p class="text-muted small mb-0">Écart Net</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Info Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <p class="text-muted small mb-1">Réalisé par</p>
                    <p class="fw-semibold mb-0">{{ $inventaire->user?->name ?? '—' }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <p class="text-muted small mb-1">Produits avec manquants</p>
                    <p class="fw-semibold mb-0 text-danger">{{ $manquantsCount }} produit(s)</p>
                </div>
                <div class="col-12 col-md-4">
                    <p class="text-muted small mb-1">Produits avec surplus</p>
                    <p class="fw-semibold mb-0 text-success">{{ $surplusCount }} produit(s)</p>
                </div>
                @if($inventaire->observation)
                    <div class="col-12">
                        <p class="text-muted small mb-1">Observation</p>
                        <p class="mb-0">{{ $inventaire->observation }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-table me-2 text-primary"></i>Détail par Produit
            </h6>
            <div class="d-flex gap-2">
                @if($manquantsCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $manquantsCount }} manquant(s)</span>
                @endif
                @if($surplusCount > 0)
                    <span class="badge bg-success rounded-pill">{{ $surplusCount }} surplus</span>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-paginated align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th class="text-center">Stock Théorique</th>
                        <th class="text-center">Stock Physique</th>
                        <th class="text-center">Écart</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventaire->inventaireDetails as $i => $detail)
                        <tr class="{{ $detail->ecart < 0 ? 'table-danger bg-opacity-25' : ($detail->ecart > 0 ? 'table-success bg-opacity-25' : '') }}">
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $detail->produit?->nom ?? '—' }}</div>
                                @if($detail->produit?->reference)
                                    <small class="text-muted">Réf: {{ $detail->produit->reference }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    {{ $detail->produit?->categorie?->nom ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($detail->stock_theorique) }}</td>
                            <td class="text-center fw-medium">{{ number_format($detail->stock_physique) }}</td>
                            <td class="text-center fw-bold {{ $detail->ecart < 0 ? 'text-danger' : ($detail->ecart > 0 ? 'text-success' : 'text-muted') }}">
                                {{ $detail->ecart >= 0 ? '+' : '' }}{{ number_format($detail->ecart) }}
                            </td>
                            <td class="text-center">
                                @if($detail->ecart < 0)
                                    <span class="badge bg-danger"><i class="bi bi-arrow-down me-1"></i>Manquant</span>
                                @elseif($detail->ecart > 0)
                                    <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>Surplus</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-check me-1"></i>Conforme</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL</td>
                        <td class="text-center">{{ number_format($totalTheorique) }}</td>
                        <td class="text-center">{{ number_format($totalPhysique) }}</td>
                        <td class="text-center {{ $totalEcart < 0 ? 'text-danger' : ($totalEcart > 0 ? 'text-success' : 'text-muted') }}">
                            {{ $totalEcart >= 0 ? '+' : '' }}{{ number_format($totalEcart) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <style>
    @media print {
        .btn, nav, aside, header { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    }
    </style>
</x-app-layout>
