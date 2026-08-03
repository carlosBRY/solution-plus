<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Ventes & Caisse</p>
                <h1 class="h3 mb-0">Gestion des Ventes</h1>
            </div>
            <div>
                @can('gérer-ventes')
                    <a class="btn btn-primary btn-sm" href="{{ route('ventes.create') }}">
                        <i class="bi bi-cart-plus me-1"></i> Nouvelle Vente / Caisse
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

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Ventes Réalisées</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalVentesCount }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Ventes du Jour</span>
                <h3 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($ventesAujourdhui, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Chiffre d'Affaires Global</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($totalChiffreAffaires, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-cart-check me-2"></i>Journal des Ventes</h2>
                <p class="text-muted mb-0">Historique des encaissements et des reçus délivrés aux clients.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('ventes.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="N° ticket, client..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="PAYEE" {{ request('statut') === 'PAYEE' ? 'selected' : '' }}>Payée (Comptant)</option>
                            <option value="PAYEE_CREDIT" {{ request('statut') === 'PAYEE_CREDIT' ? 'selected' : '' }}>Crédit Réglé</option>
                            <option value="EN_ATTENTE" {{ request('statut') === 'EN_ATTENTE' ? 'selected' : '' }}>Crédit en attente</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-paginated align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Date Vente</th>
                        <th>Client</th>
                        <th>Vendeur</th>
                        <th>Mode Règlement</th>
                        <th>Total Net</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventes as $v)
                        <tr>
                            <td><a href="{{ route('ventes.show', $v) }}" class="fw-bold text-decoration-none">{{ $v->numero }}</a></td>
                            <td>{{ $v->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $v->client ? $v->client->nom . ' ' . $v->client->prenom : 'Client Comptant' }}</td>
                            <td>{{ $v->user->name }}</td>
                            <td>
                                @php $p = $v->paiements->first(); @endphp
                                <span class="badge bg-light text-body border">{{ $v->is_credit ? 'Crédit' : ($p ? $p->mode : '—') }}</span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($v->total, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @if($v->statut->value === 'PAYEE_CREDIT' || $v->statut === \App\Enums\StatutVente::PAYEE_CREDIT)
                                    <span class="badge bg-info text-dark" title="Crédit entièrement réglé par le client">
                                        <i class="bi bi-check-all me-1"></i>Crédit Réglé
                                    </span>
                                    @if($v->date_paiement_credit)
                                        <small class="d-block text-muted fs-7">Réglé le {{ $v->date_paiement_credit->format('d/m/Y') }}</small>
                                    @endif
                                @elseif($v->statut->value === 'PAYEE' || $v->statut === \App\Enums\StatutVente::PAYEE)
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Payée (Comptant)</span>
                                @elseif($v->statut->value === 'EN_ATTENTE' || $v->statut === \App\Enums\StatutVente::EN_ATTENTE)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Crédit en attente</span>
                                @else
                                    <span class="badge bg-secondary">Annulée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('ventes.show', $v) }}" class="btn btn-outline-primary btn-sm" title="Imprimer le Reçu">
                                    <i class="bi bi-receipt"></i> Voir Reçu
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune vente enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ventes->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $ventes->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
