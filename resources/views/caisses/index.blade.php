<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Finances & Trésorerie</p>
                <h1 class="h3 mb-0">Gestion de la Caisse</h1>
            </div>
            <div class="d-flex gap-2">
                @can('gérer-caisses')
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeposerCaisseIndex">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter de l'argent
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRetirerCaisseIndex">
                        <i class="bi bi-dash-circle me-1"></i> Retirer de l'argent
                    </button>
                @endcan
                @if(!$caisseOuverte)
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalOuvrirCaisse">
                        <i class="bi bi-unlock me-1"></i> Ouvrir la Caisse
                    </button>
                @else
                    <button type="button" class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalFermerCaisse">
                        <i class="bi bi-lock me-1"></i> Clôturer la Caisse
                    </button>
                @endif
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

    {{-- Active Cash Session Section --}}
    @if($caisseOuverte && $statsOuverte)
        <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="spinner-grow spinner-grow-sm text-light" role="status"></span>
                    <h2 class="h5 mb-0 fw-bold">Session de Caisse Active</h2>
                </div>
                <span class="badge bg-light text-primary fw-bold px-3 py-2">OUVERTE</span>
            </div>
            <div class="card-body p-4">
                {{-- Global Summary --}}
                <div class="row g-4 align-items-center mb-4">
                    <div class="col-12 col-md-3 border-end">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Caissier responsable</small>
                        <h5 class="fw-bold mb-2">{{ $caisseOuverte->user?->name ?? 'N/A' }}</h5>
                        <small class="text-muted d-block"><i class="bi bi-clock me-1"></i>Ouvert le : {{ $caisseOuverte->date_ouverture ? $caisseOuverte->date_ouverture->format('d/m/Y H:i') : '—' }}</small>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Solde Initial Total</small>
                        <h4 class="fw-bold mb-0 text-secondary">{{ number_format($statsOuverte['solde_initial'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h4>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Encaissé</small>
                        <h4 class="fw-bold mb-0 text-success">+ {{ number_format($statsOuverte['total_encaissements'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h4>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Dépenses Sorties</small>
                        <h4 class="fw-bold mb-0 text-danger">- {{ number_format($statsOuverte['total_depenses'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h4>
                    </div>
                    <div class="col-12 col-md-3 bg-light rounded p-3 text-center">
                        <small class="text-uppercase fw-bold text-primary d-block mb-1">Solde Théorique Global</small>
                        <h3 class="fw-extrabold text-primary mb-2">{{ number_format($statsOuverte['solde_theorique'], 0, ',', ' ') }} FCFA</h3>
                        <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalFermerCaisse">
                            <i class="bi bi-lock me-1"></i> Clôturer & Enregistrer
                        </button>
                    </div>
                </div>

                {{-- Per-Account Breakdown Table --}}
                <h6 class="fw-bold mb-2"><i class="bi bi-bank me-2 text-primary"></i>Détail par Compte de Paiement</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Compte</th>
                                <th class="text-end">Solde Initial</th>
                                <th class="text-end text-success">Ventes</th>
                                <th class="text-end text-success">Règlements</th>
                                <th class="text-end text-danger">Dépenses</th>
                                <th class="text-end fw-bold">Solde Théorique</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statsOuverte['by_account'] as $compteId => $acc)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $acc['nom'] }}</span>
                                        <small class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $acc['mode'] }}</small>
                                    </td>
                                    <td class="text-end text-muted">{{ number_format($acc['solde_initial'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-success">+{{ number_format($acc['ventes'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-success">+{{ number_format($acc['reglements'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-danger">-{{ number_format($acc['depenses'], 0, ',', ' ') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($acc['solde_theorique'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>TOTAL CONSOLIDÉ</td>
                                <td class="text-end">{{ number_format($statsOuverte['solde_initial'], 0, ',', ' ') }}</td>
                                <td class="text-end text-success">+{{ number_format($statsOuverte['total_ventes'], 0, ',', ' ') }}</td>
                                <td class="text-end text-success">+{{ number_format($statsOuverte['total_reglements'], 0, ',', ' ') }}</td>
                                <td class="text-end text-danger">-{{ number_format($statsOuverte['total_depenses'], 0, ',', ' ') }}</td>
                                <td class="text-end text-primary fs-5">{{ number_format($statsOuverte['solde_theorique'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-4 text-center">
                <div class="avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-exclamation-triangle fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1">Aucune caisse ouverte actuellement</h4>
                <p class="text-muted mb-3">Veuillez ouvrir la caisse enregistreuse pour démarrer les encaissements de la journée.</p>
                <button type="button" class="btn btn-success px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalOuvrirCaisse">
                    <i class="bi bi-unlock me-2"></i> Ouvrir la Caisse Enregistreuse
                </button>
            </div>
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Nombre Total de Sessions</span>
                <h3 class="fw-bold mb-0 mt-1">{{ $totalCaisses }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-semibold">Cumul des Écarts de Caisse</span>
                <h3 class="fw-bold mb-0 mt-1 {{ $totalEcarts < 0 ? 'text-danger' : ($totalEcarts > 0 ? 'text-success' : 'text-secondary') }}">
                    {{ number_format($totalEcarts, 0, ',', ' ') }} FCFA
                </h3>
            </div>
        </div>
    </div>

    {{-- History Panel --}}
    <section class="panel">
        <div class="panel-header flex-column flex-md-row align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-journal-text me-2"></i>Historique des Sessions de Caisse</h2>
                <p class="text-muted mb-0">Consultez et révisez le détail des fermetures de caisse précédentes.</p>
            </div>
            <div>
                <form method="GET" action="{{ route('caisses.index') }}" class="row g-2">
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="OUVERTE" {{ request('statut') === 'OUVERTE' ? 'selected' : '' }}>Ouverte</option>
                            <option value="FERMEE" {{ request('statut') === 'FERMEE' ? 'selected' : '' }}>Fermée</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input class="form-control form-control-sm" type="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ouverture</th>
                        <th>Fermeture</th>
                        <th>Caissier</th>
                        <th>Solde Initial</th>
                        <th>Solde Final</th>
                        <th>Écart</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caisses as $c)
                        <tr>
                            <td class="fw-semibold">{{ $c->date_ouverture ? $c->date_ouverture->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $c->date_fermeture ? $c->date_fermeture->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $c->user?->name ?? 'N/A' }}</td>
                            <td>{{ number_format($c->solde_initial, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $c->solde_final !== null ? number_format($c->solde_final, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            <td>
                                @if($c->solde_final !== null)
                                    @if($c->ecart == 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">0 FCFA (Conforme)</span>
                                    @elseif($c->ecart < 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ number_format($c->ecart, 0, ',', ' ') }} FCFA (Manquant)</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">+{{ number_format($c->ecart, 0, ',', ' ') }} FCFA (Surplus)</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($c->statut->value === 'OUVERTE' || $c->statut === \App\Enums\StatutCaisse::OUVERTE)
                                    <span class="badge bg-success">Ouverte</span>
                                @else
                                    <span class="badge bg-secondary">Fermée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('caisses.show', $c) }}" class="btn btn-outline-primary btn-sm" title="Voir le Rapport">
                                    <i class="bi bi-eye me-1"></i> Rapport
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune session de caisse enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($caisses->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <span class="text-muted small">Affichage de {{ $caisses->firstItem() }} à {{ $caisses->lastItem() }} sur {{ $caisses->total() }}</span>
                {{ $caisses->links() }}
            </div>
        @endif
    </section>

    {{-- =============================== --}}
    {{-- Modal Ouverture — Per Account   --}}
    {{-- =============================== --}}
    <div class="modal fade" id="modalOuvrirCaisse" tabindex="-1" aria-labelledby="modalOuvrirCaisseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('caisses.ouvrir') }}">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalOuvrirCaisseLabel"><i class="bi bi-unlock me-2"></i>Ouverture de la Caisse</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info small py-2 mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Les soldes initiaux sont pré-remplis avec les soldes de la dernière clôture (reconduction automatique).
                            Vérifiez et ajustez si nécessaire.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Compte / Mode de Paiement</th>
                                        <th class="text-end" style="width: 200px;">Solde Initial (FCFA)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comptesActifs as $compte)
                                        @php
                                            $soldeReconduit = $soldesReconduction[$compte->id]['solde'] ?? (float) $compte->solde_courant;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $compte->nom }}</div>
                                                <small class="text-muted">{{ $compte->mode }}</small>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       class="form-control text-end solde-initial-input"
                                                       name="soldes_initiaux[{{ $compte->id }}]"
                                                       value="{{ $soldeReconduit }}"
                                                       required>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td class="fw-bold">TOTAL</td>
                                        <td class="text-end">
                                            <span id="totalSoldeInitial" class="fw-bold fs-5 text-primary">0</span> FCFA
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success fw-bold" data-confirm="Confirmer l'ouverture de la caisse avec ce fond de roulement ?"><i class="bi bi-check-lg me-1"></i> Valider l'Ouverture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =============================== --}}
    {{-- Modal Clôture — Per Account     --}}
    {{-- =============================== --}}
    @if($caisseOuverte && $statsOuverte)
        <div class="modal fade" id="modalFermerCaisse" tabindex="-1" aria-labelledby="modalFermerCaisseLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('caisses.fermer', $caisseOuverte) }}">
                        @csrf
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="modalFermerCaisseLabel"><i class="bi bi-lock me-2"></i>Clôture de la Caisse</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-muted small mb-3">
                                Saisissez le solde réellement constaté/compté pour chaque compte de paiement. L'écart sera calculé automatiquement.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Compte</th>
                                            <th class="text-end">Solde Théorique</th>
                                            <th class="text-end" style="width: 200px;">Solde Compté (FCFA)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statsOuverte['by_account'] as $compteId => $acc)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $acc['nom'] }}</div>
                                                    <small class="text-muted">{{ $acc['mode'] }}</small>
                                                </td>
                                                <td class="text-end text-primary fw-semibold">
                                                    {{ number_format($acc['solde_theorique'], 0, ',', ' ') }} FCFA
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           step="0.01"
                                                           min="0"
                                                           class="form-control text-end solde-final-input"
                                                           name="soldes_finaux[{{ $compteId }}]"
                                                           value="{{ $acc['solde_theorique'] }}"
                                                           required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td class="fw-bold">TOTAL</td>
                                            <td class="text-end fw-bold text-primary fs-5">{{ number_format($statsOuverte['solde_theorique'], 0, ',', ' ') }} FCFA</td>
                                            <td class="text-end">
                                                <span id="totalSoldeFinal" class="fw-bold fs-5 text-primary">0</span> FCFA
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning fw-bold text-dark" data-confirm="Confirmer la clôture définitive de la session de caisse ?"><i class="bi bi-lock-fill me-1"></i> Confirmer la Clôture</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Ajouter de l'argent (Dépôt) --}}
    <div class="modal fade" id="modalDeposerCaisseIndex" tabindex="-1" aria-hidden="true">
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
                            <label for="caisse_deposer_compte_id" class="form-label fw-medium">Caisse / Compte Destination <span class="text-danger">*</span></label>
                            <select id="caisse_deposer_compte_id" name="compte_id" class="form-select" required>
                                <option value="">Choisir la caisse...</option>
                                @foreach($comptesActifs as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde actuel: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="caisse_deposer_montant" class="form-label fw-medium">Montant à ajouter (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="caisse_deposer_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 50000">
                        </div>
                        <div class="mb-3">
                            <label for="caisse_deposer_motif" class="form-label fw-medium">Motif de l'apport d'argent <span class="text-danger">*</span></label>
                            <input type="text" id="caisse_deposer_motif" name="motif" class="form-control" required placeholder="Ex: Apport personnel, Fond de caisse initial...">
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
    <div class="modal fade" id="modalRetirerCaisseIndex" tabindex="-1" aria-hidden="true">
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
                            <label for="caisse_retirer_compte_id" class="form-label fw-medium">Caisse / Compte Source <span class="text-danger">*</span></label>
                            <select id="caisse_retirer_compte_id" name="compte_id" class="form-select" required>
                                <option value="">Choisir la caisse...</option>
                                @foreach($comptesActifs as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nom }} (Solde actuel: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="caisse_retirer_montant" class="form-label fw-medium">Montant à retirer (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="caisse_retirer_montant" name="montant" class="form-control form-control-lg" min="1" required placeholder="Ex: 25000">
                        </div>
                        <div class="mb-3">
                            <label for="caisse_retirer_motif" class="form-label fw-medium">Motif du retrait <span class="text-danger">*</span></label>
                            <input type="text" id="caisse_retirer_motif" name="motif" class="form-control" required placeholder="Ex: Prélèvement gérant, Versement banque...">
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function updateTotal(selector, outputId) {
            const inputs = document.querySelectorAll(selector);
            const output = document.getElementById(outputId);
            if (!output) return;
            let total = 0;
            inputs.forEach(function(input) {
                total += parseFloat(input.value) || 0;
                input.addEventListener('input', function() {
                    let t = 0;
                    inputs.forEach(function(i) { t += parseFloat(i.value) || 0; });
                    output.textContent = t.toLocaleString('fr-FR');
                });
            });
            output.textContent = total.toLocaleString('fr-FR');
        }
        updateTotal('.solde-initial-input', 'totalSoldeInitial');
        updateTotal('.solde-final-input', 'totalSoldeFinal');
    });
    </script>
</x-app-layout>
