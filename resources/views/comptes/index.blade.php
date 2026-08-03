<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Finances & Trésorerie</p>
                <h1 class="h3 mb-0">Caisse Principale & Comptes Financiers</h1>
            </div>
            <div class="d-flex gap-2">
                @can('gérer-caisses')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeposer">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter de l'argent
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRetirer">
                        <i class="bi bi-dash-circle me-1"></i> Retirer de l'argent
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTransfert">
                        <i class="bi bi-arrow-left-right me-1"></i> Nouveau Transfert
                    </button>
                @endcan
                @can('gérer-parametres')
                    <a href="{{ route('parametres.comptes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-gear me-1"></i> Configurer Comptes
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

    {{-- KPI Header Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-white-50 text-uppercase fw-semibold small mb-1">Caisse Principale (Solde Total Consolidé)</p>
                        <h2 class="display-6 fw-bold mb-0">{{ number_format($soldeTotal, 0, ',', ' ') }} <small class="fs-5">FCFA</small></h2>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-20 p-3">
                        <i class="bi bi-wallet2 fs-1 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10">
                        <i class="bi bi-arrow-down-left-circle fs-3 text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Entrées aujourd'hui</p>
                        <h4 class="fw-bold mb-0 text-success">+{{ number_format($totalCreditsJour, 0, ',', ' ') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10">
                        <i class="bi bi-arrow-up-right-circle fs-3 text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Sorties aujourd'hui</p>
                        <h4 class="fw-bold mb-0 text-danger">-{{ number_format($totalDebitsJour, 0, ',', ' ') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid des Comptes --}}
    <h5 class="fw-bold mb-3"><i class="bi bi-bank me-2 text-primary"></i>États des Comptes par Moyen de Paiement</h5>
    <div class="row g-3 mb-4">
        @forelse($comptes as $compte)
            @php
                $iconClass = match($compte->mode) {
                    'ESPECES' => 'bi-cash-stack text-success',
                    'ORANGE_MONEY' => 'bi-phone text-warning',
                    'MOOV_MONEY' => 'bi-phone-fill text-primary',
                    'WAVE' => 'bi-water text-info',
                    'CARTE' => 'bi-credit-card text-primary',
                    'VIREMENT' => 'bi-bank text-secondary',
                    default => 'bi-wallet2 text-dark'
                };
            @endphp
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fs-3"><i class="bi {{ $iconClass }}"></i></span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 small fw-mono">
                                    {{ $compte->mode }}
                                </span>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $compte->nom }}</h6>
                            <p class="text-muted small mb-3 text-truncate">{{ $compte->description ?? 'Compte actif' }}</p>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Solde Actuel</p>
                            <h3 class="fw-bold text-dark mb-3">
                                {{ number_format($compte->solde_courant, 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small>
                            </h3>
                            <a href="{{ route('comptes.show', $compte) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-journal-text me-1"></i> Voir le Journal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-4 mb-0">
                    Aucun compte financier actif. Veuillez configurer vos comptes dans les paramètres.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Journal Récent des Mouvements Globaux --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-clock-history me-2 text-primary"></i>Derniers Mouvements Financiers
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Heure</th>
                        <th>Compte</th>
                        <th class="text-center">Type</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Solde Après</th>
                        <th>Motif</th>
                        <th>Auteur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mouvementsRecents as $mouvement)
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-medium">{{ $mouvement->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $mouvement->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $mouvement->compteFinancier?->nom ?? '—' }}</span>
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
                            <td class="text-end fw-bold ">
                                {{ $mouvement->type === 'CREDIT' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-end fw-medium text-muted">
                                {{ number_format($mouvement->solde_apres, 0, ',', ' ') }} FCFA
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $mouvement->motif }}">
                                    {{ $mouvement->motif }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $mouvement->user?->name ?? 'Système' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Aucun mouvement financier enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Transfert inter-comptes --}}
    <div class="modal fade" id="modalTransfert" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('comptes.transferer') }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Nouveau Transfert entre Comptes
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="source_id" class="form-label fw-medium">Compte Source (Débiter) <span class="text-danger">*</span></label>
                            <select id="source_id" name="source_id" class="form-select" required>
                                <option value="">Choisir le compte source...</option>
                                @foreach($comptes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="destination_id" class="form-label fw-medium">Compte Destination (Créditer) <span class="text-danger">*</span></label>
                            <select id="destination_id" name="destination_id" class="form-select" required>
                                <option value="">Choisir le compte destination...</option>
                                @foreach($comptes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="montant" class="form-label fw-medium">Montant du Transfert (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 50000">
                        </div>
                        <div class="mb-3">
                            <label for="motif" class="form-label fw-medium">Motif du transfert <span class="text-danger">*</span></label>
                            <input type="text" id="motif" name="motif" class="form-control" required placeholder="Ex: Dépôt espèces en banque, Rechargement Wave...">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" data-confirm="Confirmer ce transfert de fonds entre comptes ?"><i class="bi bi-check-lg me-1"></i> Valider le Transfert</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Ajouter de l'argent (Dépôt) --}}
    <div class="modal fade" id="modalDeposer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('comptes.deposer') }}">
                    @csrf
                    <div class="modal-header bg-success text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter de l'argent (Dépôt en Caisse)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="deposer_compte_id" class="form-label fw-medium">Caisse / Compte Destination <span class="text-danger">*</span></label>
                            <select id="deposer_compte_id" name="compte_id" class="form-select" required>
                                <option value="">Choisir la caisse...</option>
                                @foreach($comptes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde actuel: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="deposer_montant" class="form-label fw-medium">Montant à ajouter (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="deposer_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 50000">
                        </div>
                        <div class="mb-3">
                            <label for="deposer_motif" class="form-label fw-medium">Motif de l'apport d'argent <span class="text-danger">*</span></label>
                            <input type="text" id="deposer_motif" name="motif" class="form-control" required placeholder="Ex: Apport personnel, Alimentation de caisse du jour, Fond de caisse...">
                            <small class="text-muted">Le motif est obligatoire pour la traçabilité comptable.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" data-confirm="Confirmer l'ajout de fonds dans la caisse ?"><i class="bi bi-check-lg me-1"></i> Valider l'Ajout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Retirer de l'argent (Retrait) --}}
    <div class="modal fade" id="modalRetirer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('comptes.retirer') }}">
                    @csrf
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-dash-circle me-2"></i>Retirer de l'argent (Retrait de Caisse)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="retirer_compte_id" class="form-label fw-medium">Caisse / Compte Source <span class="text-danger">*</span></label>
                            <select id="retirer_compte_id" name="compte_id" class="form-select" required>
                                <option value="">Choisir la caisse...</option>
                                @foreach($comptes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde actuel: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="retirer_montant" class="form-label fw-medium">Montant à retirer (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="retirer_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 25000">
                        </div>
                        <div class="mb-3">
                            <label for="retirer_motif" class="form-label fw-medium">Motif du retrait <span class="text-danger">*</span></label>
                            <input type="text" id="retirer_motif" name="motif" class="form-control" required placeholder="Ex: Prélèvement gérant, Versement banque, Avance...">
                            <small class="text-muted">Le motif est obligatoire pour la traçabilité comptable.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger" data-confirm="Confirmer le retrait de fonds de la caisse ?"><i class="bi bi-check-lg me-1"></i> Valider le Retrait</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
