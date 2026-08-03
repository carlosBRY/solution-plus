<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Gestion Commerciale</p>
                <h1 class="h3 mb-0">Gestion des Clients & Crédits</h1>
            </div>
            <div>
                @can('gérer-clients')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createClientModal">
                        <i class="bi bi-person-plus me-1"></i> Nouveau Client
                    </button>
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
                <span class="text-muted small fw-semibold">Total Clients</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalClients }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Clients Endettés (Crédits en cours)</span>
                <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $clientsEndettesCount }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Encours Dettes / Créances</span>
                <h3 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($totalCreances, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-people me-2"></i>Répertoire des Clients</h2>
                <p class="text-muted mb-0">Suivez les achats des clients, leurs crédits en cours et plafonds de dette.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('clients.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Nom, téléphone, email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="dette_filter">
                            <option value="">Tous les clients</option>
                            <option value="avec_dette" {{ request('dette_filter') === 'avec_dette' ? 'selected' : '' }}>Avec dette en cours</option>
                            <option value="sans_dette" {{ request('dette_filter') === 'sans_dette' ? 'selected' : '' }}>Sans dette</option>
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
                        <th>Client</th>
                        <th>Téléphone / Email</th>
                        <th>Adresse</th>
                        <th>Dette Actuelle (Solde)</th>
                        <th>Plafond Crédit</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <a href="{{ route('clients.show', $client) }}" class="fw-bold text-decoration-none">{{ $client->nom }}</a>
                            </td>
                            <td>
                                <div>{{ $client->telephone ?? '—' }}</div>
                                <small class="text-muted">{{ $client->email ?? '' }}</small>
                            </td>
                            <td>{{ $client->adresse ?? '—' }}</td>
                            <td>
                                @if($client->solde > 0)
                                    <span class="badge bg-danger-subtle text-danger fs-6 fw-bold">
                                        {{ number_format($client->solde, 0, ',', ' ') }} FCFA
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success">0 FCFA (À jour)</span>
                                @endif
                            </td>
                            <td>
                                @if($client->plafond_credit > 0)
                                    <span class="badge bg-light text-body border">{{ number_format($client->plafond_credit, 0, ',', ' ') }} FCFA</span>
                                @else
                                    <span class="text-muted small">Non plafonné</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-primary btn-sm me-1" title="Voir la fiche">
                                    <i class="bi bi-eye"></i> Fiche
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editClientModal-{{ $client->id }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteClientModal-{{ $client->id }}" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Edit Modal --}}
                                <div class="modal fade text-start" id="editClientModal-{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('clients.update', $client) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier {{ $client->nom }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nom du Client <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="nom" value="{{ old('nom', $client->nom) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Téléphone</label>
                                                        <input class="form-control" type="tel" name="telephone" value="{{ old('telephone', $client->telephone) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Adresse E-mail</label>
                                                        <input class="form-control" type="email" name="email" value="{{ old('email', $client->email) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Adresse Physique</label>
                                                        <input class="form-control" type="text" name="adresse" value="{{ old('adresse', $client->adresse) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Plafond de Crédit Autorisé (FCFA)</label>
                                                        <input class="form-control" type="number" step="0.01" name="plafond_credit" value="{{ old('plafond_credit', $client->plafond_credit) }}">
                                                        <div class="form-text text-muted small">0 pour un crédit illimité.</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Delete Modal --}}
                                <div class="modal fade text-start" id="deleteClientModal-{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Supprimer Client</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer le client <strong>"{{ $client->nom }}"</strong> ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun client enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $clients->links() }}
            </div>
        @endif
    </section>

    {{-- Create Modal --}}
    <div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('clients.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nouveau Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom Complet <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nom" placeholder="ex: KOUASSI Jean-Baptiste" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input class="form-control" type="tel" name="telephone" placeholder="ex: +225 07 08 09 10 11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse E-mail</label>
                            <input class="form-control" type="email" name="email" placeholder="client@exemple.ci">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse Physique</label>
                            <input class="form-control" type="text" name="adresse" placeholder="ex: Cocody Angré 8ème Tranche">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Plafond Crédit (FCFA)</label>
                                <input class="form-control" type="number" step="0.01" name="plafond_credit" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Solde Dette Initial</label>
                                <input class="form-control" type="number" step="0.01" name="solde_initial" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
