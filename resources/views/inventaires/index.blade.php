<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Inventaire & Logistique</p>
                <h1 class="h3 mb-0">Historique des Inventaires</h1>
            </div>
            @can('gérer-inventaires')
                <a href="{{ route('inventaires.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-clipboard-plus me-1"></i> Nouvel Inventaire Physique
                </a>
            @endcan
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
                        <i class="bi bi-clipboard-check fs-4 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Inventaires</p>
                        <h3 class="fw-bold mb-0">{{ $totalInventaires }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10">
                        <i class="bi bi-arrow-down-circle fs-4 text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Écarts Négatifs</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalEcartsNegatifs) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10">
                        <i class="bi bi-arrow-up-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Écarts Positifs</p>
                        <h3 class="fw-bold mb-0 text-success">+{{ number_format($totalEcartsPositifs) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventaires.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="date" class="form-label small fw-medium">Filtrer par date</label>
                    <input id="date" type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('inventaires.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Inventaires Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-clipboard-data me-2 text-primary"></i>Liste des Inventaires
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $inventaires->total() }} inventaire(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Heure</th>
                        <th>Réalisé par</th>
                        <th class="text-center">Nb. Produits</th>
                        <th class="text-center">Écarts Négatifs</th>
                        <th class="text-center">Écarts Positifs</th>
                        <th>Observation</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventaires as $inventaire)
                        @php
                            $ecartsNeg = $inventaire->inventaireDetails->where('ecart', '<', 0)->sum('ecart');
                            $ecartsPos = $inventaire->inventaireDetails->where('ecart', '>', 0)->sum('ecart');
                            $nbProduits = $inventaire->inventaireDetails->count();
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-medium">{{ $inventaire->date->format('d/m/Y') }}</span>
                                <br><small class="text-muted">{{ $inventaire->date->format('H:i') }}</small>
                            </td>
                            <td>{{ $inventaire->user?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill">{{ $nbProduits }}</span>
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ $ecartsNeg != 0 ? number_format($ecartsNeg) : '—' }}
                            </td>
                            <td class="text-center fw-bold text-success">
                                {{ $ecartsPos > 0 ? '+'.number_format($ecartsPos) : '—' }}
                            </td>
                            <td>
                                <span class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="{{ $inventaire->observation }}">
                                    {{ $inventaire->observation ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('inventaires.show', $inventaire) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i> Rapport
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard fs-2 d-block mb-2"></i>
                                Aucun inventaire enregistré pour le moment.
                                @can('gérer-inventaires')
                                    <br><a href="{{ route('inventaires.create') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="bi bi-clipboard-plus me-1"></i> Réaliser le premier inventaire
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inventaires->hasPages())
            <div class="card-footer bg-transparent border-top pt-3">
                {{ $inventaires->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
