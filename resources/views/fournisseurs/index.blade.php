<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Achat & Partenaires</p>
                <h1 class="h3 mb-0">Gestion des Fournisseurs</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createFournisseurModal">
                    <i class="bi bi-building-add me-1"></i> Nouveau Fournisseur
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Fournisseurs Référencés</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalFournisseurs }}</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-building me-2"></i>Répertoire des Fournisseurs</h2>
                <p class="text-muted mb-0">Partenaires et maisons de négoce approvisionnant la cave.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('fournisseurs.index') }}" class="d-flex gap-2">
                    <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Nom, ville, téléphone..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-paginated align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Téléphone / Email</th>
                        <th>Ville & Pays</th>
                        <th>Adresse</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $f)
                        <tr>
                            <td>
                                <a href="{{ route('fournisseurs.show', $f) }}" class="fw-bold text-decoration-none">{{ $f->nom }}</a>
                            </td>
                            <td>
                                <div>{{ $f->telephone ?? '—' }}</div>
                                <small class="text-muted">{{ $f->email ?? '' }}</small>
                            </td>
                            <td>{{ $f->ville ? $f->ville . ($f->pays ? ', ' . $f->pays : '') : '—' }}</td>
                            <td>{{ $f->adresse ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('fournisseurs.show', $f) }}" class="btn btn-outline-primary btn-sm me-1" title="Voir les livraisons">
                                    <i class="bi bi-eye"></i> Fiche
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editFournisseurModal-{{ $f->id }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteFournisseurModal-{{ $f->id }}" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Edit Modal --}}
                                <div class="modal fade text-start" id="editFournisseurModal-{{ $f->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('fournisseurs.update', $f) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier {{ $f->nom }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nom du Fournisseur <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" name="nom" value="{{ old('nom', $f->nom) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Téléphone</label>
                                                        <input class="form-control" type="tel" name="telephone" value="{{ old('telephone', $f->telephone) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Adresse E-mail</label>
                                                        <input class="form-control" type="email" name="email" value="{{ old('email', $f->email) }}">
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Ville</label>
                                                            <input class="form-control" type="text" name="ville" value="{{ old('ville', $f->ville) }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Pays</label>
                                                            <input class="form-control" type="text" name="pays" value="{{ old('pays', $f->pays) }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Adresse Physique</label>
                                                        <input class="form-control" type="text" name="adresse" value="{{ old('adresse', $f->adresse) }}">
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
                                <div class="modal fade text-start" id="deleteFournisseurModal-{{ $f->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('fournisseurs.destroy', $f) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Supprimer Fournisseur</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer le fournisseur <strong>"{{ $f->nom }}"</strong> ?
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
                            <td colspan="5" class="text-center text-muted py-4">Aucun fournisseur enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fournisseurs->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $fournisseurs->firstItem() }} à {{ $fournisseurs->lastItem() }} sur {{ $fournisseurs->total() }}</span>
                {{ $fournisseurs->links() }}
            </div>
        @endif
    </section>

    {{-- Create Modal --}}
    <div class="modal fade" id="createFournisseurModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('fournisseurs.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-building-add me-2"></i>Nouveau Fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom de l'entreprise / Maison <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nom" placeholder="ex: Maison Champy & Fils" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input class="form-control" type="tel" name="telephone" placeholder="ex: +225 27 20 21 22 23">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse E-mail</label>
                            <input class="form-control" type="email" name="email" placeholder="contact@maisonchampy.fr">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Ville</label>
                                <input class="form-control" type="text" name="ville" placeholder="ex: Beaune / Abidjan">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pays</label>
                                <input class="form-control" type="text" name="pays" value="France">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse Physique</label>
                            <input class="form-control" type="text" name="adresse" placeholder="ex: 12 Rue du Faubourg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le Fournisseur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
