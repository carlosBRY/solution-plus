<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Inventaire & Logistique</p>
                <h1 class="h3 mb-0">Gestion du Stock</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('stocks.mouvements') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left-right me-1"></i> Journal des Mouvements
                </a>
                @can('gérer-inventaires')
                    <a href="{{ route('inventaires.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-clipboard-plus me-1"></i> Nouvel Inventaire
                    </a>
                @endcan
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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                        <i class="bi bi-layers fs-4 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Unités en Stock</p>
                        <h3 class="fw-bold mb-0">{{ number_format($totalUnitesEnStock) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10">
                        <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Produits en Alerte</p>
                        <h3 class="fw-bold mb-0 text-warning">{{ $produitsEnAlerte }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10">
                        <i class="bi bi-x-circle fs-4 text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Produits en Rupture</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ $produitsEnRupture }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stocks.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label small fw-medium">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="search" type="text" name="search" class="form-control"
                               placeholder="Nom, référence, code barre…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label for="categorie_id" class="form-label small fw-medium">Catégorie</label>
                    <select id="categorie_id" name="categorie_id" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $categorie)
                            <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                {{ $categorie->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label for="statut" class="form-label small fw-medium">Statut Stock</label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="alerte" {{ request('statut') === 'alerte' ? 'selected' : '' }}>⚠ En Alerte</option>
                        <option value="rupture" {{ request('statut') === 'rupture' ? 'selected' : '' }}>✕ En Rupture</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stock Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-table me-2 text-primary"></i>État des Stocks
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $produits->total() }} produit(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th class="text-center">Stock Actuel</th>
                        <th class="text-center">Seuil Min.</th>
                        <th class="text-center">Statut</th>
                        @can('gérer-stocks')
                        <th class="text-end">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $produit)
                        @php
                            $stockQte = $produit->stock?->quantite ?? 0;
                            $stockMin = $produit->stock_min ?? 0;
                            $isRupture = $stockQte <= 0;
                            $isAlerte = !$isRupture && $stockQte <= $stockMin;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $produit->nom }}</div>
                                <small class="text-muted">
                                    @if($produit->reference)Réf: {{ $produit->reference }}@endif
                                    @if($produit->code_barre) · {{ $produit->code_barre }}@endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    {{ $produit->categorie?->nom ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center fw-bold {{ $isRupture ? 'text-danger' : ($isAlerte ? 'text-warning' : 'text-success') }}">
                                {{ number_format($stockQte) }} {{ $produit->unite_base }}
                            </td>
                            <td class="text-center text-muted">{{ $stockMin }} {{ $produit->unite_base }}</td>
                            <td class="text-center">
                                @if($isRupture)
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rupture</span>
                                @elseif($isAlerte)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Alerte</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Normal</span>
                                @endif
                            </td>
                            @can('gérer-stocks')
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAjuster"
                                        data-produit-id="{{ $produit->id }}"
                                        data-produit-nom="{{ $produit->nom }}"
                                        data-produit-stock="{{ $stockQte }}"
                                        data-produit-unite="{{ $produit->unite_base }}"
                                        title="Ajuster le stock">
                                    <i class="bi bi-pencil-square"></i> Ajuster
                                </button>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-layers fs-2 d-block mb-2"></i>
                                Aucun produit trouvé pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($produits->hasPages())
            <div class="card-footer bg-transparent border-top pt-3">
                {{ $produits->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Ajustement --}}
    @can('gérer-stocks')
    <div class="modal fade" id="modalAjuster" tabindex="-1" aria-labelledby="modalAjusterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="formAjuster" method="POST" action="">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="modalAjusterLabel">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Ajuster le Stock
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-info-circle"></i>
                            <div>Stock actuel : <strong id="ajustStockActuel">—</strong></div>
                        </div>
                        <div class="mb-3">
                            <label for="ajustQuantite" class="form-label fw-medium">
                                Nouvelle quantité <span id="ajustUnite" class="text-muted small"></span>
                            </label>
                            <input type="number" id="ajustQuantite" name="quantite" class="form-control form-control-lg"
                                   min="0" required placeholder="Ex: 150">
                        </div>
                        <div class="mb-3">
                            <label for="ajustMotif" class="form-label fw-medium">Motif de l'ajustement <span class="text-danger">*</span></label>
                            <textarea id="ajustMotif" name="motif" class="form-control" rows="3"
                                      required placeholder="Ex: Correction suite à comptage physique…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Valider l'ajustement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalAjuster = document.getElementById('modalAjuster');
        modalAjuster.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const produitId  = btn.dataset.produitId;
            const produitNom = btn.dataset.produitNom;
            const stockActuel = btn.dataset.produitStock;
            const unite = btn.dataset.produitUnite;

            modalAjuster.querySelector('#modalAjusterLabel').innerHTML =
                '<i class="bi bi-pencil-square me-2 text-primary"></i>Ajuster : ' + produitNom;
            modalAjuster.querySelector('#ajustStockActuel').textContent = stockActuel + ' ' + unite;
            modalAjuster.querySelector('#ajustUnite').textContent = '(' + unite + ')';
            modalAjuster.querySelector('#ajustQuantite').value = stockActuel;

            const form = modalAjuster.querySelector('#formAjuster');
            form.action = '/stocks/' + produitId + '/ajuster';
        });
    });
    </script>
    @endcan
</x-app-layout>
