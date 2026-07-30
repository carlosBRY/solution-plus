<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Achat & Stock</p>
                <h1 class="h3 mb-0">Gestion des Approvisionnements</h1>
            </div>
            <div>
                <a class="btn btn-primary btn-sm" href="{{ route('approvisionnements.create') }}">
                    <i class="bi bi-plus-circle me-1"></i> Nouvel Approvisionnement
                </a>
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

    {{-- Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Approvisionnements</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalApprovisionnements }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">En Attente de Réception</span>
                <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $enAttenteCount }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Réceptionné (Achats)</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($totalMontantReçus, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-truck me-2"></i>Historique des Livraisons</h2>
                <p class="text-muted mb-0">Suivi des achats auprès des fournisseurs et réception des stocks en conditionnements.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('approvisionnements.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="N° Bon, Fournisseur..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="fournisseur_id">
                            <option value="">Tous les fournisseurs</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}" {{ request('fournisseur_id') == $f->id ? 'selected' : '' }}>{{ $f->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="RECEPTIONNE" {{ request('statut') === 'RECEPTIONNE' ? 'selected' : '' }}>Réceptionné</option>
                            <option value="EN_ATTENTE" {{ request('statut') === 'EN_ATTENTE' ? 'selected' : '' }}>En attente</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Commande / Facture</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Saisi par</th>
                        <th>Montant Total</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvisionnements as $app)
                        <tr>
                            <td><a href="{{ route('approvisionnements.show', $app) }}" class="fw-bold text-decoration-none">{{ $app->numero }}</a></td>
                            <td>{{ $app->date->format('d/m/Y H:i') }}</td>
                            <td><span class="fw-semibold">{{ $app->fournisseur->nom }}</span></td>
                            <td>{{ $app->user->name }}</td>
                            <td class="fw-bold text-primary">{{ number_format($app->total, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @if($app->statut->value === 'RECEPTIONNE' || $app->statut === \App\Enums\StatutApprovisionnement::RECEPTIONNE)
                                    <span class="badge bg-success">Réceptionné</span>
                                @elseif($app->statut->value === 'EN_ATTENTE' || $app->statut === \App\Enums\StatutApprovisionnement::EN_ATTENTE)
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                    <span class="badge bg-secondary">Annulé</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('approvisionnements.show', $app) }}" class="btn btn-outline-primary btn-sm me-1" title="Voir les détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($app->statut->value === 'EN_ATTENTE' || $app->statut === \App\Enums\StatutApprovisionnement::EN_ATTENTE)
                                    <form method="POST" action="{{ route('approvisionnements.receptionner', $app) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirmer la réception physique de la marchandise et créditer les stocks ?')">
                                            <i class="bi bi-box-arrow-in-down me-1"></i> Réceptionner
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun approvisionnement enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($approvisionnements->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $approvisionnements->firstItem() }} à {{ $approvisionnements->lastItem() }} sur {{ $approvisionnements->total() }}</span>
                {{ $approvisionnements->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
