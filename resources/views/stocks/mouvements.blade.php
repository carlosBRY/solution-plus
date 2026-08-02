<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Inventaire & Logistique</p>
                <h1 class="h3 mb-0">Journal des Mouvements de Stock</h1>
            </div>
            <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour au Stock
            </a>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stocks.mouvements') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label small fw-medium">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="search" type="text" name="search" class="form-control"
                               placeholder="Produit, motif, référence…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <label for="type" class="form-label small fw-medium">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach(\App\Enums\MouvementType::cases() as $type)
                            <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>
                                {{ $type->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_debut" class="form-label small fw-medium">Du</label>
                    <input id="date_debut" type="date" name="date_debut" class="form-control"
                           value="{{ request('date_debut') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_fin" class="form-label small fw-medium">Au</label>
                    <input id="date_fin" type="date" name="date_fin" class="form-control"
                           value="{{ request('date_fin') }}">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('stocks.mouvements') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Mouvements Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-arrow-left-right me-2 text-primary"></i>Historique des Mouvements
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $mouvements->total() }} mouvement(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-paginated align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Heure</th>
                        <th>Produit</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Qté Avant</th>
                        <th class="text-center">Qté Après</th>
                        <th class="text-center">Variation</th>
                        <th>Motif / Référence</th>
                        <th>Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mouvements as $mouvement)
                        @php
                            $variation = $mouvement->quantite_apres - $mouvement->quantite_avant;
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-medium">{{ $mouvement->created_at->format('d/m/Y') }}</span>
                                <br><small class="text-muted">{{ $mouvement->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $mouvement->produit?->nom ?? '—' }}</div>
                                @if($mouvement->conditionnement)
                                    <small class="text-muted">{{ $mouvement->conditionnement->nom }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $typeColors = [
                                        'ENTREE'       => 'success',
                                        'SORTIE'       => 'danger',
                                        'AJUSTEMENT'   => 'info',
                                        'RETOUR'       => 'warning',
                                        'STOCK_INITIAL'=> 'primary',
                                        'DETERIORATION'=> 'dark',
                                    ];
                                    $color = $typeColors[$mouvement->type->value ?? ''] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-15  border border-{{ $color }} border-opacity-25">
                                    {{ $mouvement->type->value ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center text-muted">{{ number_format($mouvement->stock_avant) }}</td>
                            <td class="text-center fw-medium">{{ number_format($mouvement->stock_apres) }}</td>
                            <td class="text-center fw-bold {{ $variation >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $variation >= 0 ? '+' : '' }}{{ number_format($variation) }}
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $mouvement->motif }}">
                                    {{ $mouvement->motif ?? '—' }}
                                </div>
                                @if($mouvement->reference)
                                    <small class="text-muted">Réf: {{ $mouvement->reference }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $mouvement->user?->name ?? 'Système' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-right fs-2 d-block mb-2"></i>
                                Aucun mouvement trouvé pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mouvements->hasPages())
            <div class="card-footer bg-transparent border-top pt-3">
                {{ $mouvements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
