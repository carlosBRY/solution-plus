<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Catalogue & Stock</p>
                <h1 class="h3 mb-0">Gestion des Produits & Conditionnements</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProduitModal">
                    <i class="bi bi-box-seam me-1" aria-hidden="true"></i> Nouveau Produit
                </button>
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

    {{-- Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Produits</span>
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-box-seam fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($totalProduits, 0, ',', ' ') }}</h3>
                <span class="text-muted small">Références en catalogue</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Stock Total (Unités de base)</span>
                    <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="bi bi-layers fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($totalStockBouteilles, 0, ',', ' ') }}</h3>
                <span class="text-muted small">Stock réel calculé</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Alertes de Stock Bas</span>
                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="bi bi-exclamation-triangle fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $produitsAlerteCount }}</h3>
                <span class="text-muted small">Produits sous le seuil min</span>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Inventaire des Produits & conditionnements</span></h2>
                <p class="text-muted mb-0">Chaque produit est géré en unité de base avec ses conditionnements multiples (bouteille, pack, caisse).</p>
            </div>
            <div class="w-100 w-md-auto">
                <form method="GET" action="{{ route('produits.index') }}" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Nom, Réf, Code..." value="{{ request('search') }}" aria-label="Rechercher">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="categorie_id">
                            <option value="">Toutes catégories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('categorie_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="stock_status">
                            <option value="">Tous les stocks</option>
                            <option value="alerte" {{ request('stock_status') === 'alerte' ? 'selected' : '' }}>En alerte</option>
                            <option value="epuise" {{ request('stock_status') === 'epuise' ? 'selected' : '' }}>Épuisé</option>
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
                        <th>Photo</th>
                        <th>Produit & Unité</th>
                        <th>Conditionnements Configurés</th>
                        <th>Prix Réf.</th>
                        <th>Stock Disponible (Caisses / Bouteilles)</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $produit)
                        @php
                            $quantite = $produit->stock?->quantite ?? 0;
                            $enAlerte = $quantite <= $produit->stock_min;
                        @endphp
                        <tr>
                            <td>
                                @if($produit->photo)
                                    <img src="{{ asset('storage/' . $produit->photo) }}" alt="{{ $produit->nom }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                @else
                                    <span class="badge bg-primary-subtle text-primary p-2 rounded d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bi bi-cup-straw fs-5"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <p class="fw-bold mb-0">{{ $produit->nom }}</p>
                                    <span class="badge bg-light text-body border me-1">{{ $produit->reference }}</span>
                                    <span class="badge bg-secondary-subtle text-body">Unité: {{ $produit->unite_base }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($produit->conditionnements as $cond)
                                        <span class="badge bg-info-subtle text-info border">
                                            {{ $cond->nom }} (x{{ $cond->quantite_unite_base }})
                                        </span>
                                    @endforeach
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="modal" data-bs-target="#addCondModal-{{ $produit->id }}" title="Ajouter un conditionnement">
                                        <i class="bi bi-plus-circle-fill text-primary"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="small">Achat: {{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</div>
                                <div class="fw-bold text-success">Vente: {{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</div>
                            </td>
                            <td>
                                @if($quantite <= 0)
                                    <span class="badge bg-danger">Épuisé (0 {{ $produit->unite_base }})</span>
                                @elseif($enAlerte)
                                    <span class="badge bg-warning text-dark fw-bold">⚠️ {{ $produit->stock_formate }}</span>
                                    <div class="small text-muted">Alerte min: {{ $produit->stock_min }} bts</div>
                                @else
                                    <span class="badge bg-success-subtle text-success fw-bold">{{ $produit->stock_formate }}</span>
                                @endif
                            </td>
                            <td>
                                @if($produit->actif)
                                    <span class="badge text-bg-success">Actif</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editProduitModal-{{ $produit->id }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteProduitModal-{{ $produit->id }}" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Add Conditionnement Modal --}}
                                <div class="modal fade text-start" id="addCondModal-{{ $produit->id }}" tabindex="-1" aria-labelledby="addCondLabel-{{ $produit->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('produits.conditionnements.store', $produit) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addCondLabel-{{ $produit->id }}"><i class="bi bi-box-seam me-2"></i>Nouveau Conditionnement pour {{ $produit->nom }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nom du conditionnement <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="nom" placeholder="ex: Pack de 6, Caisse de 12" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre d'unités de base ({{ $produit->unite_base }}) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" name="quantite_unite_base" min="1" value="6" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Prix Achat Optionnel (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_achat">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Prix Vente Optionnel (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_vente">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_achat" value="1" checked id="achat-{{ $produit->id }}">
                                                            <label class="form-check-label" for="achat-{{ $produit->id }}">Utilisable pour achats</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_vente" value="1" checked id="vente-{{ $produit->id }}">
                                                            <label class="form-check-label" for="vente-{{ $produit->id }}">Utilisable pour ventes</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Ajouter Conditionnement</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Modal --}}
                                <div class="modal fade text-start" id="editProduitModal-{{ $produit->id }}" tabindex="-1" aria-labelledby="editProduitLabel-{{ $produit->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('produits.update', $produit) }}" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editProduitLabel-{{ $produit->id }}"><i class="bi bi-pencil me-2"></i>Modifier {{ $produit->nom }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Nom du Produit <span class="text-danger">*</span></label>
                                                            <input class="form-control" type="text" name="nom" value="{{ old('nom', $produit->nom) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="categorie_id" required>
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat->id }}" {{ old('categorie_id', $produit->categorie_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Référence <span class="text-danger">*</span></label>
                                                            <input class="form-control" type="text" name="reference" value="{{ old('reference', $produit->reference) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Unité de Base en Stock <span class="text-danger">*</span></label>
                                                            <input class="form-control" type="text" name="unite_base" value="{{ old('unite_base', $produit->unite_base) }}" required placeholder="ex: BOUTEILLE, CANETTE, CANNON">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prix Achat Unité (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_achat" value="{{ old('prix_achat', $produit->prix_achat) }}" placeholder="0">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prix Vente Unité (FCFA)</label>
                                                            <input class="form-control" type="number" step="0.01" name="prix_vente" value="{{ old('prix_vente', $produit->prix_vente) }}" placeholder="0">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Seuil d'Alerte Min <span class="text-danger">*</span></label>
                                                            <input class="form-control" type="number" name="stock_min" value="{{ old('stock_min', $produit->stock_min) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Photo du Produit</label>
                                                            @if($produit->photo)
                                                                <div class="mb-2 d-flex align-items-center gap-2">
                                                                    <img src="{{ asset('storage/' . $produit->photo) }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                                                    <span class="small text-muted">Image actuelle</span>
                                                                </div>
                                                            @endif
                                                            <input class="form-control" type="file" name="photo" accept="image/*">
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="actif" value="1" id="edit_actif_{{ $produit->id }}" {{ old('actif', $produit->actif) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold text-dark" for="edit_actif_{{ $produit->id }}">
                                                                    Produit Actif (Disponible à la vente & dans le catalogue)
                                                                </label>
                                                            </div>
                                                        </div>
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
                                <div class="modal fade text-start" id="deleteProduitModal-{{ $produit->id }}" tabindex="-1" aria-labelledby="deleteProduitLabel-{{ $produit->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('produits.destroy', $produit) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteProduitLabel-{{ $produit->id }}"><i class="bi bi-exclamation-triangle me-2"></i>Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer le produit <strong>"{{ $produit->nom }}"</strong> (Réf: {{ $produit->reference }}) ?
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
                            <td colspan="7" class="text-center text-muted py-4">Aucun produit trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($produits->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $produits->links() }}
            </div>
        @endif
    </section>

    {{-- Create Modal --}}
    <div class="modal fade" id="createProduitModal" tabindex="-1" aria-labelledby="createProduitLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('produits.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProduitLabel"><i class="bi bi-box-seam me-2"></i>Nouveau Produit / Cru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="create_nom">Nom du Produit <span class="text-danger">*</span></label>
                                <input class="form-control @error('nom') is-invalid @enderror" id="create_nom" type="text" name="nom" value="{{ old('nom') }}" placeholder="ex: Moët & Chandon Brut Impérial" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_cat">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('categorie_id') is-invalid @enderror" id="create_cat" name="categorie_id" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categorie_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                                    @endforeach
                                </select>
                                @error('categorie_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_unite">Unité de Stock de Référence <span class="text-danger">*</span></label>
                                <input class="form-control @error('unite_base') is-invalid @enderror" id="create_unite" type="text" name="unite_base" value="{{ old('unite_base', 'BOUTEILLE') }}" placeholder="ex: BOUTEILLE, CANETTE" required>
                                @error('unite_base')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_ref">Référence (Optionnel)</label>
                                <input class="form-control @error('reference') is-invalid @enderror" id="create_ref" type="text" name="reference" value="{{ old('reference') }}" placeholder="Laissez vide pour générer automatiquement">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_pa">Prix d'Achat Unité (FCFA)</label>
                                <input class="form-control @error('prix_achat') is-invalid @enderror" id="create_pa" type="number" step="0.01" name="prix_achat" value="{{ old('prix_achat') }}" placeholder="0">
                                @error('prix_achat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_pv">Prix de Vente Unité (FCFA)</label>
                                <input class="form-control @error('prix_vente') is-invalid @enderror" id="create_pv" type="number" step="0.01" name="prix_vente" value="{{ old('prix_vente') }}" placeholder="0">
                                @error('prix_vente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_qte">Stock Initial de Départ (Unités)</label>
                                <input class="form-control @error('quantite_initiale') is-invalid @enderror" id="create_qte" type="number" name="quantite_initiale" value="{{ old('quantite_initiale', 0) }}">
                                <div class="form-text text-muted small">Générera un mouvement de type STOCK_INITIAL.</div>
                                @error('quantite_initiale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_min">Seuil d'Alerte Minimum <span class="text-danger">*</span></label>
                                <input class="form-control @error('stock_min') is-invalid @enderror" id="create_min" type="number" name="stock_min" value="{{ old('stock_min', 5) }}" required>
                                @error('stock_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="create_photo">Photo du Produit</label>
                                <input class="form-control @error('photo') is-invalid @enderror" id="create_photo" type="file" name="photo" accept="image/*">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="create_desc">Description / Note de dégustation</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="create_desc" name="description" rows="3" placeholder="Description du produit...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer le Produit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
