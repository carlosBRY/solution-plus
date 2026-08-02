<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <p class="eyebrow mb-1 text-muted">Gestion du Parc d'Emballages</p>
                <h1 class="h3 mb-0">Casiers & Bouteilles Consignées</h1>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                    <i class="bi bi-plus-circle me-1"></i> Nouveau Type de Casier
                </button>
                <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#initStockGlobalModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Ajouter des Caisses & Bouteilles
                </button>
                <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#createMouvementModal">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Vendre (Consigner) Casiers & Bouteilles
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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 bg-primary text-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-white-50 uppercase fw-semibold">Casiers Vides en Cave</span>
                    <i class="bi bi-box-seam fs-3 opacity-75"></i>
                </div>
                <div class="display-6 fw-extrabold">{{ number_format($totalCasiersCave) }}</div>
                <small class="text-white-50 mt-1">Stock physique appartenant à la cave</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 bg-info text-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-white-50 uppercase fw-semibold">Bouteilles Seules en Cave</span>
                    <i class="bi bi-cup-straw fs-3 opacity-75"></i>
                </div>
                <div class="display-6 fw-extrabold">{{ number_format($totalBouteillesSeulesCave) }}</div>
                <small class="text-white-50 mt-1">Emballages hors casier complet</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 bg-warning text-dark h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-dark-50 uppercase fw-semibold">Prêtés aux Clients (À Récupérer)</span>
                    <i class="bi bi-arrow-up-right-circle fs-3 opacity-75"></i>
                </div>
                <div class="display-6 fw-extrabold">{{ number_format($totalCasiersPretes) }} <span class="fs-6 text-muted fw-normal">casiers</span></div>
                <small class="text-dark-50 mt-1">+ {{ number_format($totalBouteillesPretees) }} bouteille(s) chez les clients</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 bg-secondary text-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-white-50 uppercase fw-semibold">Déposés en Garde à la Cave</span>
                    <i class="bi bi-arrow-down-left-circle fs-3 opacity-75"></i>
                </div>
                <div class="display-6 fw-extrabold">{{ number_format($totalCasiersDeposes) }} <span class="fs-6 text-muted fw-normal">casiers</span></div>
                <small class="text-white-50 mt-1">+ {{ number_format($totalBouteillesDeposees) }} bouteille(s) en consigne chez nous</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Liste des Types de Casiers et Stock Cave --}}
        <div class="col-12 col-xl-4">
            <section class="panel">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-boxes me-2"></i>Stock Casiers en Cave</h2>
                        <small class="text-muted">Types & Emballages physiques</small>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($typeCasiers as $type)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $type->nom }}</h6>
                                    <small class="text-muted">Capacité : {{ $type->capacite_bouteilles }} bouteilles / casier</small>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editStockModal-{{ $type->id }}" title="Mettre à jour le stock">
                                    <i class="bi bi-pencil-square"></i> Stock
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 small">
                                <div>
                                    <span class="fw-bold text-primary fs-6">{{ number_format($type->quantite_casiers_cave) }}</span>
                                    <span class="text-muted">casier(s)</span>
                                </div>
                                <div>
                                    <span class="fw-bold text-info fs-6">{{ number_format($type->quantite_bouteilles_seules_cave) }}</span>
                                    <span class="text-muted">bouteille(s) seule(s)</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aucun type de casier configuré. Cliquez sur "Nouveau Type de Casier" pour démarrer.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Registre des Mouvements / Consignations --}}
        <div class="col-12 col-xl-8">
            <section class="panel">
                <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-journal-text me-2"></i>Registre des Prêts & Dépôts</h2>
                        <small class="text-muted">Suivi des consignations en cours et historisées</small>
                    </div>

                    {{-- Filtres rapide --}}
                    <form method="GET" action="{{ route('casiers.index') }}" class="d-flex gap-2">
                        <select name="statut" class="form-select form-select-sm ts-ignore" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="EN_COURS" {{ request('statut') === 'EN_COURS' ? 'selected' : '' }}>En cours (Non soldé)</option>
                            <option value="SOLDE" {{ request('statut') === 'SOLDE' ? 'selected' : '' }}>Soldé (Restitué)</option>
                        </select>
                        <select name="type_mouvement" class="form-select form-select-sm ts-ignore" onchange="this.form.submit()">
                            <option value="">Tous les types</option>
                            <option value="PRET_CLIENT" {{ request('type_mouvement') === 'PRET_CLIENT' ? 'selected' : '' }}>Prêt Client (Sortie)</option>
                            <option value="DEPOT_CAVE" {{ request('type_mouvement') === 'DEPOT_CAVE' ? 'selected' : '' }}>Dépôt en Cave (Entrée)</option>
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-paginated align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date & Agent</th>
                                <th>Client / Personne</th>
                                <th>Type Mouvement</th>
                                <th>Casiers & Bouteilles</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consignations as $consignation)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $consignation->date_mouvement->format('d/m/Y H:i') }}</div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $consignation->user->name }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $consignation->nom_affichage }}</div>
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $consignation->contact_affichage }}</small>
                                    </td>
                                    <td>
                                        @if($consignation->type_mouvement === 'PRET_CLIENT')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-right me-1"></i>Prêt au client</span>
                                        @else
                                            <span class="badge bg-info text-dark"><i class="bi bi-arrow-down-left me-1"></i>Dépôt chez la cave</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $consignation->typeCasier->nom }}</div>
                                        <small class="text-muted">
                                            <strong>{{ $consignation->nombre_casiers }}</strong> casier(s) &bull;
                                            <strong>{{ $consignation->nombre_bouteilles_seules }}</strong> bouteille(s)
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @if($consignation->statut === 'EN_COURS')
                                            <span class="badge bg-danger"><i class="bi bi-clock-history me-1"></i>En cours</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Soldé</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($consignation->statut === 'EN_COURS')
                                            <form method="POST" action="{{ route('casiers.mouvements.solder', $consignation) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Confirmez-vous la restitution / le solde de cette consignation ?')" title="Marquer comme restitué / soldé">
                                                    <i class="bi bi-check2-all me-1"></i> Solder
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small"><i class="bi bi-check-all me-1"></i>Terminé</span>
                                        @endif
                                        <form method="POST" action="{{ route('casiers.mouvements.destroy', $consignation) }}" class="d-inline ms-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce mouvement ? Le stock sera corrigé automatiquement.')" title="Annuler et supprimer ce mouvement (erreur de saisie)">
                                                <i class="bi bi-trash me-1"></i> Annuler
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Aucun mouvement de casier / bouteille enregistré.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Modal: Nouveau Type de Casier --}}
    <div class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('casiers.types.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTypeModalLabel"><i class="bi bi-box-seam me-2"></i>Créer un Type de Casier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="nom">Nom du Casier <span class="text-danger">*</span></label>
                            <input class="form-control" id="nom" type="text" name="nom" placeholder="Ex: Casier 12 Bouteilles" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="capacite_bouteilles">Capacité (Nombre de Bouteilles par Casier) <span class="text-danger">*</span></label>
                            <input class="form-control" id="capacite_bouteilles" type="number" name="capacite_bouteilles" value="12" min="1" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold" for="quantite_casiers_cave">Stock Casiers Vides</label>
                                <input class="form-control" id="quantite_casiers_cave" type="number" name="quantite_casiers_cave" value="0" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold" for="quantite_bouteilles_seules_cave">Bouteilles Seules</label>
                                <input class="form-control" id="quantite_bouteilles_seules_cave" type="number" name="quantite_bouteilles_seules_cave" value="0" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="description">Notes / Description (Optionnel)</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Description libre..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Créer le Type</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Vendre (Consigner) ou Déposer des Casiers & Bouteilles --}}
    <div class="modal fade" id="createMouvementModal" tabindex="-1" aria-labelledby="createMouvementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('casiers.mouvements.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="createMouvementModalLabel"><i class="bi bi-box-arrow-up-right me-2"></i>Vendre (Consigner) ou Déposer des Casiers & Bouteilles</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Information :</strong> La vente/consignation d'emballages au client décrémente automatiquement le stock physique disponible en cave.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="type_mouvement">Nature du Mouvement <span class="text-danger">*</span></label>
                                <select class="form-select fw-bold ts-ignore" id="type_mouvement" name="type_mouvement" required>
                                    <option value="PRET_CLIENT">🏷️ VENDRE / CONSIGNER AU CLIENT (Sortie : le stock cave diminue)</option>
                                    <option value="DEPOT_CAVE">📥 DÉPÔT / RETOUR EN CAVE (Entrée : le stock cave augmente)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="type_casier_id">Type de Casier <span class="text-danger">*</span></label>
                                <select class="form-select" id="type_casier_id" name="type_casier_id" required>
                                    <option value="">Sélectionner un type de casier</option>
                                    @foreach($typeCasiers as $t)
                                        <option value="{{ $t->id }}">{{ $t->nom }} (Stock Cave: {{ $t->quantite_casiers_cave }} casiers, {{ $t->quantite_bouteilles_seules_cave }} bouteilles)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="client_id_casier">Client Enregistré</label>
                            <select class="form-select" id="client_id_casier" name="client_id">
                                <option value="">— Personne non enregistrée (Saisir ci-dessous) —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->nom }} {{ $client->prenom }} ({{ $client->telephone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3 bg-light p-2 rounded" id="boxPassantCasier">
                            <div class="col-6">
                                <label class="form-label small text-muted" for="nom_personne">Nom & Prénom (Passant)</label>
                                <input class="form-control form-control-sm" id="nom_personne" type="text" name="nom_personne" placeholder="Ex: M. Yao">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted" for="contact_personne">Téléphone / Contact</label>
                                <input class="form-control form-control-sm" id="contact_personne" type="text" name="contact_personne" placeholder="Ex: 07 00 00 00 00">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold" for="nombre_casiers">Nombre de Casiers <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm text-center fw-bold fs-5" id="nombre_casiers" type="number" name="nombre_casiers" value="1" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold" for="nombre_bouteilles_seules">Nombre de Bouteilles Seules</label>
                                <input class="form-control form-control-sm text-center fw-bold fs-5" id="nombre_bouteilles_seules" type="number" name="nombre_bouteilles_seules" value="0" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes_mouvement">Notes / Motif (Optionnel)</label>
                            <textarea class="form-control" id="notes_mouvement" name="notes" rows="2" placeholder="Informations complémentaires..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Enregistrer la Vente / Consignation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modals: Ajuster le Stock Physique pour chaque Type de Casier --}}
    @foreach($typeCasiers as $type)
        <div class="modal fade" id="editStockModal-{{ $type->id }}" tabindex="-1" aria-labelledby="editStockModalLabel-{{ $type->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('casiers.types.adjust-stock', $type) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStockModalLabel-{{ $type->id }}"><i class="bi bi-pencil-square me-2"></i>Ajuster Stock Cave : {{ $type->nom }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Stock Casiers Vides en Cave</label>
                                <input class="form-control" type="number" name="quantite_casiers_cave" value="{{ $type->quantite_casiers_cave }}" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Stock Bouteilles Seules en Cave</label>
                                <input class="form-control" type="number" name="quantite_bouteilles_seules_cave" value="{{ $type->quantite_bouteilles_seules_cave }}" min="0" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Modal: Ajouter des Caisses & Bouteilles en Cave --}}
    <div class="modal fade" id="initStockGlobalModal" tabindex="-1" aria-labelledby="initStockGlobalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('casiers.initialiser-stock') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="initStockGlobalModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Ajouter / Entrée de Caisses & Bouteilles en Cave</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Saisissez le nombre de caisses/casiers et bouteilles achetés ou payés pour les ajouter directement à votre stock disponible en cave.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="init_type_casier_id">Type de Casier <span class="text-danger">*</span></label>
                            <select class="form-select fw-semibold ts-ignore" id="init_type_casier_id" name="type_casier_id" required>
                                @foreach($typeCasiers as $t)
                                    <option value="{{ $t->id }}">{{ $t->nom }} (Stock Actuel: {{ $t->quantite_casiers_cave }} casiers, {{ $t->quantite_bouteilles_seules_cave }} bts)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="mode_saisie">Mode d'Opération <span class="text-danger">*</span></label>
                            <select class="form-select ts-ignore" id="mode_saisie" name="mode_saisie" required>
                                <option value="AJOUTER" selected>➕ Ajouter au stock existant en cave (Achat / Entrée de caisses)</option>
                                <option value="DEFINIR">📌 Définir le stock exact (Remplacer la valeur actuelle pour correction)</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold" for="init_quantite_casiers">Nombre de Casiers / Caisses <span class="text-danger">*</span></label>
                                <input class="form-control text-center fw-bold fs-5" id="init_quantite_casiers" type="number" name="quantite_casiers_cave" value="1" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold" for="init_quantite_bouteilles">Bouteilles Seules</label>
                                <input class="form-control text-center fw-bold fs-5" id="init_quantite_bouteilles" type="number" name="quantite_bouteilles_seules_cave" value="0" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Ajouter au Stock Cave</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSelect = document.getElementById('client_id_casier');
            const boxPassant = document.getElementById('boxPassantCasier');

            if (clientSelect && boxPassant) {
                clientSelect.addEventListener('change', function() {
                    if (this.value === '') {
                        boxPassant.classList.remove('d-none');
                    } else {
                        boxPassant.classList.add('d-none');
                    }
                });
            }
        });
    </script>
</x-app-layout>
