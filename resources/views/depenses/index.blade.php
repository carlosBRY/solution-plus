<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Finances & Trésorerie</p>
                <h1 class="h3 mb-0">Gestion des Dépenses</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNouvelleDepense">
                    <i class="bi bi-plus-circle me-1"></i> Enregistrer une Dépense
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
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Dépenses du Mois En Cours</span>
                <h3 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($totalDepensesMois, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Dépenses du Jour</span>
                <h3 class="fw-bold mb-0 mt-1 text-warning text-dark">{{ number_format($totalDepensesJour, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Total Dépenses Enregistrées</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalCount }}</h3>
            </div>
        </div>
    </div>

    {{-- Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-receipt me-2"></i>Journal des Dépenses</h2>
                <p class="text-muted mb-0">Historique complet des sorties de caisse et frais de fonctionnement.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('depenses.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Mot-clé, motif..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="categorie">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('categorie') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <input class="form-control form-control-sm" type="date" name="date_debut" value="{{ request('date_debut') }}" title="Date de début">
                    </div>
                    <div class="col-auto">
                        <input class="form-control form-control-sm" type="date" name="date_fin" value="{{ request('date_fin') }}" title="Date de fin">
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
                        <th>Date</th>
                        <th>Réf. Pièce</th>
                        <th>Libellé / Motif</th>
                        <th>Catégorie</th>
                        <th>Compte Débité</th>
                        <th>Enregistré par</th>
                        <th>Montant</th>
                        <th>Observation</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depenses as $d)
                        <tr>
                            <td>{{ $d->date ? $d->date->format('d/m/Y H:i') : $d->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="fw-mono text-dark">{{ $d->reference_piece ?? '—' }}</span></td>
                            <td class="fw-bold text-dark">{{ $d->libelle }}</td>
                            <td><span class="badge bg-light text-body border">{{ $d->categorie ?? 'Divers' }}</span></td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    {{ $d->compteFinancier?->nom ?? ($d->mode ?? 'Espèces') }}
                                </span>
                            </td>
                            <td>{{ $d->user?->name ?? 'N/A' }}</td>
                            <td class="fw-bold text-danger">{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                            <td><small class="text-muted">{{ $d->observation ? Str::limit($d->observation, 40) : '—' }}</small></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditDepense{{ $d->id }}" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteDepense{{ $d->id }}" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit Depense --}}
                        <div class="modal fade" id="modalEditDepense{{ $d->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('depenses.update', $d) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la Dépense</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-3">
                                                <div class="col-7">
                                                    <label class="form-label fw-bold">Libellé / Motif <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="libelle" value="{{ $d->libelle }}" required>
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label fw-bold">Réf. Pièce Justificative <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="reference_piece" value="{{ $d->reference_piece }}" required>
                                                </div>
                                            </div>
                                            <div class="row g-3 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="categorie" required>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat }}" {{ $d->categorie === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-bold">Montant (FCFA) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0.01" class="form-control" name="montant" value="{{ $d->montant }}" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Date de la Dépense</label>
                                                <input type="datetime-local" class="form-control" name="date" value="{{ $d->date ? $d->date->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Observation / Notes</label>
                                                <textarea class="form-control" name="observation" rows="2">{{ $d->observation }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Mettre à jour</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Delete Depense --}}
                        <div class="modal fade" id="modalDeleteDepense{{ $d->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('depenses.destroy', $d) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmation</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-3 text-center">
                                            <p class="mb-1">Êtes-vous sûr de vouloir supprimer la dépense :</p>
                                            <strong class="d-block text-danger mb-2">"{{ $d->libelle }}"</strong>
                                            <small class="text-muted">Cette action est irréversible.</small>
                                        </div>
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucune dépense enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($depenses->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $depenses->firstItem() }} à {{ $depenses->lastItem() }} sur {{ $depenses->total() }}</span>
                {{ $depenses->links() }}
            </div>
        @endif
    </section>

    {{-- Modal Nouvelle Depense --}}
    <div class="modal fade" id="modalNouvelleDepense" tabindex="-1" aria-labelledby="modalNouvelleDepenseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('depenses.store') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalNouvelleDepenseLabel"><i class="bi bi-plus-circle me-2"></i>Nouvelle Dépense</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-7">
                                <label for="libelle" class="form-label fw-bold">Libellé / Motif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Ex: Achat papeterie, Transport..." required>
                            </div>
                            <div class="col-5">
                                <label for="reference_piece" class="form-label fw-bold">Réf. Pièce Justificative <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="reference_piece" name="reference_piece" placeholder="Ex: REÇU-940, FACT-0012" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="categorie" class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select" id="categorie" name="categorie" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="montant" class="form-label fw-bold">Montant (FCFA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="montant" name="montant" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="date" class="form-label fw-bold">Date & Heure de la Dépense</label>
                                <input type="datetime-local" class="form-control" id="date" name="date" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-6">
                                <label for="compte_financier_id" class="form-label fw-bold">Compte à Débiter <span class="text-danger">*</span></label>
                                <select class="form-select" id="compte_financier_id" name="compte_financier_id" required>
                                    <option value="">Sélectionner un compte...</option>
                                    @foreach($comptes as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->nom }} (Solde: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="observation" class="form-label fw-bold">Observation / Notes</label>
                            <textarea class="form-control" id="observation" name="observation" rows="2" placeholder="Informations complémentaires..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check-lg me-1"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
