<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Administration & Paramètres</p>
                <h1 class="h3 mb-0">Gestion Centralisée des Conditionnements</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGlobalCondModal">
                    <i class="bi bi-plus-circle me-1"></i> Nouveau Conditionnement
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Sub-navigation Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.index') }}">
                <i class="bi bi-gear me-1"></i> Général & Entreprise
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.comptes.index') }}">
                <i class="bi bi-wallet2 me-1"></i> Comptes & Moyens de Paiement
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('parametres.conditionnements') }}">
                <i class="bi bi-box-seam me-1"></i> Conditionnements des Produits
            </a>
        </li>
        @can('gérer-utilisateurs')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-people me-1"></i> Gestion des Utilisateurs
                </a>
            </li>
        @endcan
    </ul>

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
                <span class="text-muted small fw-semibold">Total Conditionnements</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalConditionnements }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Utilisables pour Vente</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">{{ $conditionnementsVenteCount }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Utilisables pour Approvisionnements</span>
                <h3 class="fw-bold mb-0 mt-1 text-info">{{ $conditionnementsAchatCount }}</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-box-seam me-2"></i>Catalogue des Conditionnements</h2>
                <p class="text-muted mb-0">Configurez les contenants (Pack de 6, Caisse de 12, Caisse de 24) et leurs prix par produit.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('parametres.conditionnements') }}" class="row g-2">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Nom, produit, code-barres..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="produit_id">
                            <option value="">Tous les produits</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" {{ request('produit_id') == $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                            @endforeach
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
                        <th>Conditionnement</th>
                        <th>Produit Associé</th>
                        <th>Ratio Unité Base</th>
                        <th>Prix Achat (FCFA)</th>
                        <th>Prix Vente (FCFA)</th>
                        <th>Usage</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conditionnements as $cond)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $cond->nom }}</div>
                                @if($cond->is_par_defaut)
                                    <span class="badge bg-secondary-subtle text-body">Unité de Référence Par Défaut</span>
                                @endif
                                @if($cond->code_barre)
                                    <small class="text-muted d-block">Code: {{ $cond->code_barre }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $cond->produit->nom }}</div>
                                <small class="text-muted">Réf: {{ $cond->produit->reference }} | Unité: {{ $cond->produit->unite_base }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    x{{ $cond->quantite_unite_base }} {{ $cond->produit->unite_base }}(s)
                                </span>
                            </td>
                            <td>{{ $cond->prix_achat ? number_format($cond->prix_achat, 0, ',', ' ') : '—' }}</td>
                            <td class="fw-bold text-success">{{ $cond->prix_vente ? number_format($cond->prix_vente, 0, ',', ' ') : '—' }}</td>
                            <td>
                                @if($cond->is_achat)
                                    <span class="badge bg-info-subtle text-info me-1">Achat</span>
                                @endif
                                @if($cond->is_vente)
                                    <span class="badge bg-success-subtle text-success">Vente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editCondModal-{{ $cond->id }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if(! $cond->is_par_defaut)
                                    <form method="POST" action="{{ route('parametres.conditionnements.destroy', $cond) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Supprimer ce conditionnement ?')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Edit Modal --}}
                                <div class="modal fade text-start" id="editCondModal-{{ $cond->id }}" tabindex="-1" aria-labelledby="editCondLabel-{{ $cond->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('parametres.conditionnements.update', $cond) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editCondLabel-{{ $cond->id }}"><i class="bi bi-pencil me-2"></i>Modifier {{ $cond->nom }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nom du Conditionnement <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="nom" value="{{ old('nom', $cond->nom) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre d'unités de base ({{ $cond->produit->unite_base }}) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" name="quantite_unite_base" min="1" value="{{ old('quantite_unite_base', $cond->quantite_unite_base) }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Prix d'Achat (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_achat" value="{{ old('prix_achat', $cond->prix_achat) }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Prix de Vente (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_vente" value="{{ old('prix_vente', $cond->prix_vente) }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Code-barres Spécifique</label>
                                                        <input class="form-control" type="text" name="code_barre" value="{{ old('code_barre', $cond->code_barre) }}">
                                                    </div>
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_achat" value="1" id="edit_achat-{{ $cond->id }}" {{ $cond->is_achat ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_achat-{{ $cond->id }}">Utilisable pour achats</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_vente" value="1" id="edit_vente-{{ $cond->id }}" {{ $cond->is_vente ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_vente-{{ $cond->id }}">Utilisable pour ventes</label>
                                                        </div>
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun conditionnement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conditionnements->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $conditionnements->firstItem() }} à {{ $conditionnements->lastItem() }} sur {{ $conditionnements->total() }}</span>
                {{ $conditionnements->links() }}
            </div>
        @endif
    </section>

    {{-- Create Modal --}}
    <div class="modal fade" id="createGlobalCondModal" tabindex="-1" aria-labelledby="createGlobalCondLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('parametres.conditionnements.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createGlobalCondLabel"><i class="bi bi-box-seam me-2"></i>Nouveau Conditionnement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Produit Associé <span class="text-danger">*</span></label>
                            <select class="form-select" name="produit_id" required>
                                <option value="">Sélectionner un produit</option>
                                @foreach($produits as $p)
                                    <option value="{{ $p->id }}">{{ $p->nom }} (Unité: {{ $p->unite_base }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom du Conditionnement <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nom" placeholder="ex: Pack de 6, Caisse de 12, Caisse de 24" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre d'unités de base contenues <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" name="quantite_unite_base" min="1" value="6" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Prix d'Achat (FCFA)</label>
                                <input class="form-control" type="number" step="0.01" name="prix_achat" placeholder="Optionnel">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Prix de Vente (FCFA)</label>
                                <input class="form-control" type="number" step="0.01" name="prix_vente" placeholder="Optionnel">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code-barres Spécifique</label>
                            <input class="form-control" type="text" name="code_barre" placeholder="Code EAN13 optionnel">
                        </div>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_achat" value="1" checked id="create_global_achat">
                                <label class="form-check-label" for="create_global_achat">Utilisable pour achats</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vente" value="1" checked id="create_global_vente">
                                <label class="form-check-label" for="create_global_vente">Utilisable pour ventes</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer Conditionnement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
