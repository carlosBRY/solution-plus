<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Gestion du Stock</p>
                <h1 class="h3 mb-0">Détériorations & Casses</h1>
            </div>
            <div>
                <a class="btn btn-primary btn-sm" href="{{ route('deteriorations.create') }}">
                    <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Nouvelle Déclaration
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

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Déclarations</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalDeteriorations }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">En Brouillon</span>
                <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $brouillonsCount }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Validées & Imputées</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">{{ $valideesCount }}</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-slash-circle" aria-hidden="true"></i><span>Historique des Pertes & Casses</span></h2>
                <p class="text-muted mb-0">Suivi des bouteilles et caisses détériorées ou périmées.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('deteriorations.index') }}" class="d-flex gap-2">
                    <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="N° déclaration, déclarant..." value="{{ request('search') }}" aria-label="Rechercher">
                    <select class="form-select form-select-sm" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="BROUILLON" {{ request('statut') === 'BROUILLON' ? 'selected' : '' }}>Brouillon</option>
                        <option value="VALIDEE" {{ request('statut') === 'VALIDEE' ? 'selected' : '' }}>Validée</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-paginated align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Déclaration</th>
                        <th>Date</th>
                        <th>Déclaré par</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deteriorations as $det)
                        <tr>
                            <td><a href="{{ route('deteriorations.show', $det) }}" class="fw-bold text-decoration-none">{{ $det->numero }}</a></td>
                            <td>{{ $det->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $det->user->name }}</td>
                            <td>
                                @if($det->isEstValidee())
                                    <span class="badge bg-success">Validée</span>
                                @elseif($det->isEstBrouillon())
                                    <span class="badge bg-warning text-dark">Brouillon</span>
                                @else
                                    <span class="badge bg-secondary">Annulée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('deteriorations.show', $det) }}" class="btn btn-outline-primary btn-sm me-1" title="Voir les détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($det->isEstBrouillon())
                                    <form method="POST" action="{{ route('deteriorations.destroy', $det) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Supprimer ce brouillon ?')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune détérioration enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deteriorations->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $deteriorations->firstItem() }} à {{ $deteriorations->lastItem() }} sur {{ $deteriorations->total() }}</span>
                {{ $deteriorations->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
