<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Administration</p>
                <h1 class="h3 mb-0">Paramètres des Comptes Financiers & Modes de Paiement</h1>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNouveauCompte">
                <i class="bi bi-plus-lg me-1"></i> Nouveau Compte / Mode
            </button>
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
            <a class="nav-link active" href="{{ route('parametres.comptes.index') }}">
                <i class="bi bi-wallet2 me-1"></i> Comptes & Moyens de Paiement
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.conditionnements') }}">
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

    {{-- Table des Comptes --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-bank me-2 text-primary"></i>Comptes Financiers Configurés
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $comptes->count() }} compte(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom du Compte</th>
                        <th>Code Mode</th>
                        <th class="text-end">Solde Initial</th>
                        <th class="text-end">Solde Courant</th>
                        <th class="text-center">Statut</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comptes as $compte)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $compte->nom }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-mono">
                                    {{ $compte->mode }}
                                </span>
                            </td>
                            <td class="text-end text-muted">
                                {{ number_format($compte->solde_initial, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($compte->solde_courant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-center">
                                @if($compte->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $compte->description ?? '—' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditCompte"
                                            data-id="{{ $compte->id }}"
                                            data-nom="{{ $compte->nom }}"
                                            data-description="{{ $compte->description }}"
                                            data-actif="{{ $compte->actif ? 1 : 0 }}"
                                            title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSoldeCompte"
                                            data-id="{{ $compte->id }}"
                                            data-nom="{{ $compte->nom }}"
                                            data-solde="{{ $compte->solde_courant }}"
                                            title="Ajuster/Initialiser le Solde">
                                        <i class="bi bi-cash-stack"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 fs-2 d-block mb-2"></i>
                                Aucun compte financier configuré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Nouveau Compte --}}
    <div class="modal fade" id="modalNouveauCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('parametres.comptes.store') }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Nouveau Compte / Mode de Paiement
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label fw-medium">Nom du Compte <span class="text-danger">*</span></label>
                            <input type="text" id="nom" name="nom" class="form-control" required placeholder="Ex: Wave, Orange Money, Caisse 2...">
                        </div>
                        <div class="mb-3">
                            <label for="mode" class="form-label fw-medium">Code Identifiant Mode <span class="text-danger">*</span></label>
                            <input type="text" id="mode" name="mode" class="form-control text-uppercase" required placeholder="Ex: WAVE, ORANGE_MONEY, BANQUE_SGBC...">
                            <small class="text-muted">Code unique en majuscules (ex: WAVE, MTN_MONEY, PAYPAL...)</small>
                        </div>
                        <div class="mb-3">
                            <label for="solde_initial" class="form-label fw-medium">Solde de départ (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="solde_initial" name="solde_initial" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-medium">Description / Notes</label>
                            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Détails facultatifs..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Modifier Compte --}}
    <div class="modal fade" id="modalEditCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="formEditCompte" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil me-2 text-primary"></i>Modifier le Compte
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNom" class="form-label fw-medium">Nom du Compte <span class="text-danger">*</span></label>
                            <input type="text" id="editNom" name="nom" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label fw-medium">Description</label>
                            <textarea id="editDescription" name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="editActif" name="actif" value="1">
                            <label class="form-check-label fw-medium" for="editActif">Compte actif</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Solde Compte --}}
    <div class="modal fade" id="modalSoldeCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="formSoldeCompte" method="POST" action="">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-cash-stack me-2 text-primary"></i>Régulariser le Solde
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Ce champ réinitialise manuellement le solde réel constaté sur ce compte et enregistrera un mouvement d'initialisation/régularisation.</p>
                        <div class="mb-3">
                            <label for="nouveauSolde" class="form-label fw-medium">Nouveau Solde Constaté (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" id="nouveauSolde" name="solde" class="form-control form-control-lg" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Valider le solde</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEdit = document.getElementById('modalEditCompte');
        modalEdit.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const id = btn.dataset.id;
            const nom = btn.dataset.nom;
            const description = btn.dataset.description;
            const actif = btn.dataset.actif === '1';

            modalEdit.querySelector('#editNom').value = nom;
            modalEdit.querySelector('#editDescription').value = description || '';
            modalEdit.querySelector('#editActif').checked = actif;
            modalEdit.querySelector('#formEditCompte').action = '/parametres/comptes-financiers/' + id;
        });

        const modalSolde = document.getElementById('modalSoldeCompte');
        modalSolde.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const id = btn.dataset.id;
            const solde = btn.dataset.solde;

            modalSolde.querySelector('#nouveauSolde').value = Math.round(solde);
            modalSolde.querySelector('#formSoldeCompte').action = '/parametres/comptes-financiers/' + id + '/initialiser';
        });
    });
    </script>
</x-app-layout>
