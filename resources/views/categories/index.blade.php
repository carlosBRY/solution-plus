<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Catalogue</p>
                <h1 class="h3 mb-0">Gestion des Catégories</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCategorieModal">
                    <i class="bi bi-folder-plus me-1" aria-hidden="true"></i> Nouvelle Catégorie
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Alert Messages --}}
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

    {{-- KPI Card --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-body border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Catégories</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ $totalCategories }}</h3>
                    </div>
                    <span class="badge bg-primary-subtle text-primary p-3 rounded-circle"><i class="bi bi-tags fs-4"></i></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Liste des Catégories de Vins & Spiritueux</span></h2>
                <p class="text-muted mb-0">Organisez vos crus par famille (Rouge, Blanc, Rosé, Champagne, Spiritueux, etc.).</p>
            </div>
            <div>
                <form method="GET" action="{{ route('categories.index') }}" class="d-flex gap-2">
                    <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Rechercher une catégorie..." value="{{ request('search') }}" aria-label="Rechercher">
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-paginated align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom de la Catégorie</th>
                        <th>Description</th>
                        <th>Nombre de Produits</th>
                        <th>Créée le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $categorie)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary-subtle text-body p-2 rounded"><i class="bi bi-tag-fill"></i></span>
                                    <span class="fw-bold">{{ $categorie->nom }}</span>
                                </div>
                            </td>
                            <td>{{ Str::limit($categorie->description ?? 'Aucune description', 60) }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info fw-semibold">{{ $categorie->produits_count }} produit(s)</span>
                            </td>
                            <td>{{ $categorie->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editCategorieModal-{{ $categorie->id }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCategorieModal-{{ $categorie->id }}" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Edit Modal --}}
                                <div class="modal fade text-start" id="editCategorieModal-{{ $categorie->id }}" tabindex="-1" aria-labelledby="editCategorieLabel-{{ $categorie->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('categories.update', $categorie) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editCategorieLabel-{{ $categorie->id }}">
                                                        <i class="bi bi-pencil me-2"></i>Modifier la Catégorie
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="nom-{{ $categorie->id }}">Nom de la catégorie <span class="text-danger">*</span></label>
                                                        <input class="form-control" id="nom-{{ $categorie->id }}" type="text" name="nom" value="{{ old('nom', $categorie->nom) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="desc-{{ $categorie->id }}">Description</label>
                                                        <textarea class="form-control" id="desc-{{ $categorie->id }}" name="description" rows="3">{{ old('description', $categorie->description) }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Delete Modal --}}
                                <div class="modal fade text-start" id="deleteCategorieModal-{{ $categorie->id }}" tabindex="-1" aria-labelledby="deleteCategorieLabel-{{ $categorie->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('categories.destroy', $categorie) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteCategorieLabel-{{ $categorie->id }}">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmer la suppression
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer la catégorie <strong>"{{ $categorie->nom }}"</strong> ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Supprimer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune catégorie trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $categories->firstItem() }} à {{ $categories->lastItem() }} sur {{ $categories->total() }}</span>
                {{ $categories->links() }}
            </div>
        @endif
    </section>

    {{-- Create Modal --}}
    <div class="modal fade" id="createCategorieModal" tabindex="-1" aria-labelledby="createCategorieLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategorieLabel"><i class="bi bi-folder-plus me-2"></i>Nouvelle Catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="create_nom">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input class="form-control @error('nom') is-invalid @enderror" id="create_nom" type="text" name="nom" value="{{ old('nom') }}" placeholder="ex: Vins Rouges, Champagnes, Cognacs" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="create_desc">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="create_desc" name="description" rows="3" placeholder="Description optionnelle de la gamme...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer la Catégorie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
