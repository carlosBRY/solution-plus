<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Finances & Trésorerie</p>
                <h1 class="h3 mb-0">Journal de Compte : {{ $compte->nom }}</h1>
            </div>
            <div class="d-flex gap-2">
                @can('gérer-caisses')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeposerCompte">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter de l'argent
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRetirerCompte">
                        <i class="bi bi-dash-circle me-1"></i> Retirer de l'argent
                    </button>
                @endcan
                <a href="{{ route('comptes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la Caisse Principale
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

    {{-- Info Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="fw-bold mb-0">{{ $compte->nom }}</h4>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-mono">
                                    {{ $compte->mode }}
                                </span>
                            </div>
                            <p class="text-muted small mb-0">{{ $compte->description ?? 'Compte financier actif' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <p class="text-muted small mb-0">Solde Courant Actuel</p>
                    <h2 class="display-6 fw-bold text-primary mb-0">{{ number_format($compte->solde_courant, 0, ',', ' ') }} <small class="fs-5">FCFA</small></h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-down-left-circle fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Cumulé Entrées (Crédits)</p>
                        <h4 class="fw-bold text-success mb-0">+{{ number_format($totalEntrees, 0, ',', ' ') }} FCFA</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-arrow-up-right-circle fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Cumulé Sorties (Débits)</p>
                        <h4 class="fw-bold text-danger mb-0">-{{ number_format($totalSorties, 0, ',', ' ') }} FCFA</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('comptes.show', $compte) }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label small fw-medium">Rechercher par motif</label>
                    <input id="search" type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label for="type" class="form-label small fw-medium">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="CREDIT" {{ request('type') === 'CREDIT' ? 'selected' : '' }}>Entrée (Crédit)</option>
                        <option value="DEBIT" {{ request('type') === 'DEBIT' ? 'selected' : '' }}>Sortie (Débit)</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_debut" class="form-label small fw-medium">Du</label>
                    <input id="date_debut" type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_fin" class="form-label small fw-medium">Au</label>
                    <input id="date_fin" type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                    <a href="{{ route('comptes.show', $compte) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Movements Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-journal-text me-2 text-primary"></i>Historique des Mouvements
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $mouvements->total() }} mouvement(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-paginated align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Heure</th>
                        <th class="text-center">Type</th>
                        <th class="text-end">Solde Avant</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Solde Après</th>
                        <th>Motif / Origine</th>
                        <th>Auteur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mouvements as $mouvement)
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-medium">{{ $mouvement->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $mouvement->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                @if($mouvement->type === 'CREDIT')
                                    <span class="badge bg-success bg-opacity-15  border border-success border-opacity-25">
                                        <i class="bi bi-arrow-down-left me-1"></i>ENTRÉE
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15  border border-danger border-opacity-25">
                                        <i class="bi bi-arrow-up-right me-1"></i>SORTIE
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-muted">
                                {{ number_format($mouvement->solde_avant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-end fw-bold {{ $mouvement->type === 'CREDIT' ? 'text-success' : 'text-danger' }}">
                                {{ $mouvement->type === 'CREDIT' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($mouvement->solde_apres, 0, ',', ' ') }} FCFA
                            </td>
                            <td>
                                <div>{{ $mouvement->motif }}</div>
                                @if($mouvement->reference_type)
                                    <small class="text-muted">Type: {{ strtoupper($mouvement->reference_type) }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $mouvement->user?->name ?? 'Système' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal fs-2 d-block mb-2"></i>
                                Aucun mouvement enregistré pour ce compte avec ces critères.
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

    {{-- Modal Ajouter de l'argent (Dépôt) --}}
    <div class="modal fade" id="modalDeposerCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('comptes.deposer') }}">
                    @csrf
                    <input type="hidden" name="compte_id" value="{{ $compte->id }}">
                    <div class="modal-header bg-success text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter de l'argent — {{ $compte->nom }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="deposer_show_montant" class="form-label fw-medium">Montant à ajouter (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="deposer_show_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 50000">
                        </div>
                        <div class="mb-3">
                            <label for="deposer_show_motif" class="form-label fw-medium">Motif de l'apport d'argent <span class="text-danger">*</span></label>
                            <input type="text" id="deposer_show_motif" name="motif" class="form-control" required placeholder="Ex: Apport personnel, Fond de caisse initial...">
                            <small class="text-muted">Le motif est obligatoire pour la traçabilité comptable.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" data-confirm="Confirmer l'ajout de fonds dans la caisse {{ $compte->nom }} ?"><i class="bi bi-check-lg me-1"></i> Valider l'Ajout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Retirer de l'argent (Retrait) --}}
    <div class="modal fade" id="modalRetirerCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('comptes.retirer') }}">
                    @csrf
                    <input type="hidden" name="compte_id" value="{{ $compte->id }}">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-dash-circle me-2"></i>Retirer de l'argent — {{ $compte->nom }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="retirer_show_montant" class="form-label fw-medium">Montant à retirer (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="retirer_show_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 25000">
                        </div>
                        <div class="mb-3">
                            <label for="retirer_show_motif" class="form-label fw-medium">Motif du retrait <span class="text-danger">*</span></label>
                            <input type="text" id="retirer_show_motif" name="motif" class="form-control" required placeholder="Ex: Prélèvement gérant, Versement banque...">
                            <small class="text-muted">Le motif est obligatoire pour la traçabilité comptable.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger" data-confirm="Confirmer le retrait de fonds de la caisse {{ $compte->nom }} ?"><i class="bi bi-check-lg me-1"></i> Valider le Retrait</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
