<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted"><a href="{{ route('caisses.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Caisses</a> / Rapport de Session</p>
                <h1 class="h3 mb-0">Rapport de Session de Caisse</h1>
            </div>
            <div class="d-print-none">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Imprimer le Rapport
                </button>
                <a href="{{ route('caisses.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i> Fermer
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Session Overview Header Card --}}
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-lg bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-cash-coin fs-2"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Caissier Responsable</small>
                            <h4 class="fw-bold mb-1">{{ $caisse->user?->name ?? 'N/A' }}</h4>
                            <span class="text-muted fs-7"><i class="bi bi-envelope me-1"></i>{{ $caisse->user?->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Date d'Ouverture</small>
                            <span class="fw-semibold fs-6"><i class="bi bi-clock me-1 text-success"></i>{{ $caisse->date_ouverture ? $caisse->date_ouverture->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Date de Clôture</small>
                            <span class="fw-semibold fs-6">
                                @if($caisse->date_fermeture)
                                    <i class="bi bi-clock-history me-1 text-danger"></i>{{ $caisse->date_fermeture->format('d/m/Y H:i') }}
                                @else
                                    <span class="badge bg-success">En cours (Ouverte)</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Summary Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Solde Initial Total</span>
                <h4 class="fw-bold mb-0 mt-2 text-dark">{{ number_format($stats['solde_initial'], 0, ',', ' ') }} FCFA</h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Total Encaissements</span>
                <h4 class="fw-bold mb-0 mt-2 text-success">+ {{ number_format($stats['total_encaissements'], 0, ',', ' ') }} FCFA</h4>
                <small class="text-muted fs-7">Ventes: {{ number_format($stats['total_ventes'], 0, ',', ' ') }} | Règl.: {{ number_format($stats['total_reglements'], 0, ',', ' ') }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-body border-0 shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Total Dépenses</span>
                <h4 class="fw-bold mb-0 mt-2 text-danger">- {{ number_format($stats['total_depenses'], 0, ',', ' ') }} FCFA</h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-body border-0 shadow-sm h-100 bg-primary-subtle text-primary border-primary-subtle">
                <span class="small fw-bold text-uppercase">Solde Théorique Global</span>
                <h4 class="fw-extrabold mb-0 mt-2">{{ number_format($stats['solde_theorique'], 0, ',', ' ') }} FCFA</h4>
            </div>
        </div>
    </div>

    {{-- Per-Account Breakdown Table --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-bank me-2 text-primary"></i>Bilan Ventilé par Compte de Paiement</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Compte</th>
                        <th class="text-end">Solde Initial</th>
                        <th class="text-end text-success">Ventes</th>
                        <th class="text-end text-success">Règlements</th>
                        <th class="text-end text-danger">Dépenses</th>
                        <th class="text-end fw-bold">Solde Théorique</th>
                        <th class="text-end fw-bold">Solde Compté</th>
                        <th class="text-end">Écart</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['by_account'] as $compteId => $acc)
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
                            <td class="text-end fw-bold text-primary">
                                {{ $acc['solde_final'] !== null ? number_format($acc['solde_final'], 0, ',', ' ') . ' FCFA' : '—' }}
                            </td>
                            <td class="text-end">
                                @if($acc['ecart'] !== null)
                                    @if($acc['ecart'] == 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">0 FCFA</span>
                                    @elseif($acc['ecart'] < 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ number_format($acc['ecart'], 0, ',', ' ') }} FCFA</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">+{{ number_format($acc['ecart'], 0, ',', ' ') }} FCFA</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>TOTAL CONSOLIDÉ</td>
                        <td class="text-end">{{ number_format($stats['solde_initial'], 0, ',', ' ') }}</td>
                        <td class="text-end text-success">+{{ number_format($stats['total_ventes'], 0, ',', ' ') }}</td>
                        <td class="text-end text-success">+{{ number_format($stats['total_reglements'], 0, ',', ' ') }}</td>
                        <td class="text-end text-danger">-{{ number_format($stats['total_depenses'], 0, ',', ' ') }}</td>
                        <td class="text-end text-primary fs-6">{{ number_format($stats['solde_theorique'], 0, ',', ' ') }} FCFA</td>
                        <td class="text-end text-primary fs-6">
                            {{ $caisse->solde_final !== null ? number_format($caisse->solde_final, 0, ',', ' ') . ' FCFA' : '—' }}
                        </td>
                        <td class="text-end">
                            @if($caisse->solde_final !== null)
                                @if($caisse->ecart == 0)
                                    <span class="badge bg-success">0 FCFA</span>
                                @elseif($caisse->ecart < 0)
                                    <span class="badge bg-danger">{{ number_format($caisse->ecart, 0, ',', ' ') }} FCFA</span>
                                @else
                                    <span class="badge bg-primary">+{{ number_format($caisse->ecart, 0, ',', ' ') }} FCFA</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Detail Transactions Tabs --}}
    <section class="panel">
        <ul class="nav nav-tabs card-header-tabs px-3 pt-3" id="caisseTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="ventes-tab" data-bs-toggle="tab" data-bs-target="#ventes-tab-pane" type="button" role="tab" aria-controls="ventes-tab-pane" aria-selected="true">
                    <i class="bi bi-cart-check me-1"></i> Ventes ({{ $ventes->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="reglements-tab" data-bs-toggle="tab" data-bs-target="#reglements-tab-pane" type="button" role="tab" aria-controls="reglements-tab-pane" aria-selected="false">
                    <i class="bi bi-person-check me-1"></i> Règlements Dettes ({{ $reglements->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="depenses-tab" data-bs-toggle="tab" data-bs-target="#depenses-tab-pane" type="button" role="tab" aria-controls="depenses-tab-pane" aria-selected="false">
                    <i class="bi bi-receipt me-1"></i> Dépenses Sorties ({{ $depenses->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content p-3" id="caisseTabContent">
            {{-- Tab 1: Ventes --}}
            <div class="tab-pane fade show active" id="ventes-tab-pane" role="tabpanel" aria-labelledby="ventes-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Heure / Date</th>
                                <th>N° Vente</th>
                                <th>Client</th>
                                <th>Mode</th>
                                <th class="text-end">Net Encaissé en Caisse</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventes as $p)
                                <tr>
                                    <td>{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td>
                                        @if($p->vente)
                                            <a href="{{ route('ventes.show', $p->vente) }}" class="fw-bold text-decoration-none">{{ $p->vente->numero }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $p->vente && $p->vente->client ? $p->vente->client->nom . ' ' . $p->vente->client->prenom : 'Client Comptant' }}</td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ is_object($p->mode) ? $p->mode->value : $p->mode }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-success">+ {{ number_format($p->montant, 0, ',', ' ') }} FCFA</div>
                                        @if($p->vente && $p->vente->monnaie > 0)
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                (Remis: {{ number_format($p->vente->montant_paye, 0, ',', ' ') }} | Monnaie: {{ number_format($p->vente->monnaie, 0, ',', ' ') }})
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aucune vente enregistrée durant cette session.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 2: Règlements Dettes --}}
            <div class="tab-pane fade" id="reglements-tab-pane" role="tabpanel" aria-labelledby="reglements-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Heure / Date</th>
                                <th>N° Règlement</th>
                                <th>Client</th>
                                <th>Mode</th>
                                <th>Montant Encaissé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reglements as $r)
                                <tr>
                                    <td>{{ $r->created_at ? $r->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="fw-bold">{{ $r->numero }}</td>
                                    <td>{{ $r->client ? $r->client->nom . ' ' . $r->client->prenom : 'Client inconnu' }}</td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ $r->mode }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">+ {{ number_format($r->montant, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aucun règlement de dette enregistré durant cette session.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 3: Dépenses --}}
            <div class="tab-pane fade" id="depenses-tab-pane" role="tabpanel" aria-labelledby="depenses-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Heure / Date</th>
                                <th>Libellé</th>
                                <th>Catégorie</th>
                                <th>Compte Débité</th>
                                <th>Saisi par</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($depenses as $d)
                                <tr>
                                    <td>{{ $d->created_at ? $d->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="fw-bold">{{ $d->libelle }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $d->categorie ?? 'Divers' }}</span></td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ $d->compteFinancier?->nom ?? ($d->mode ?? 'Espèces') }}
                                        </span>
                                    </td>
                                    <td>{{ $d->user ? $d->user->name : 'N/A' }}</td>
                                    <td class="fw-bold text-danger">- {{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Aucune dépense enregistrée durant cette session.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
