<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Point de Vente</p>
                <h1 class="h3 mb-0">Caisse & Nouvelle Vente</h1>
            </div>
            <div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('ventes.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Quitter la Caisse
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('ventes.store') }}" id="venteForm">
        @csrf
        <div class="row g-3">
            {{-- Panier & Sélection Produits --}}
            <div class="col-12 col-xl-8">
                <div class="panel mb-3">
                    <div class="panel-header">
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-cart3 me-2"></i>Panier d'Achat</h2>
                    </div>

                    <div id="itemsContainer">
                        <div class="item-row border rounded p-3 mb-3 bg-light-subtle" data-index="0">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Produit <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm produit-select" name="items[0][produit_id]" required>
                                        <option value="">Sélectionner un produit</option>
                                        @foreach($produits as $prod)
                                            <option value="{{ $prod->id }}">
                                                {{ $prod->nom }} (Stock: {{ $prod->stock?->quantite ?? 0 }} {{ $prod->unite_base }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm cond-select" name="items[0][conditionnement_id]" required disabled>
                                        <option value="">Choisir produit</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Qté Cond. <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm qte-input" type="number" name="items[0][quantite_conditionnement]" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Prix Vente Unitaire (FCFA) <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm prix-input" type="number" step="0.01" name="items[0][prix]" value="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter un autre produit au panier
                    </button>
                </div>
            </div>

            {{-- Panneau Encaissement & Règlement --}}
            <div class="col-12 col-xl-4">
                <div class="panel">
                    <h2 class="h5 mb-3 section-title"><i class="bi bi-cash-coin me-2"></i>Encaissement & Règlement</h2>

                    <div class="mb-3">
                        <label class="form-label" for="client_id">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">Client Comptant (Passant)</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->nom }} {{ $c->prenom }} ({{ $c->telephone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="mode_paiement">Mode de Règlement <span class="text-danger">*</span></label>
                        <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                            @foreach($modesPaiement as $m)
                                <option value="{{ $m->value }}">{{ $m->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="remise_globale">Remise Globale (FCFA)</label>
                            <input class="form-control" type="number" step="0.01" id="remise_globale" name="remise_globale" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="montant_paye">Montant Reçu (FCFA) <span class="text-danger">*</span></label>
                            <input class="form-control fw-bold fs-5 text-success" type="number" step="0.01" id="montant_paye" name="montant_paye" value="0" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg fw-bold"><i class="bi bi-check2-circle me-1"></i> Valider & Imprimer Reçu</button>
                        <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produitsData = @json($produits);
            const container = document.getElementById('itemsContainer');
            const addBtn = document.getElementById('addItemBtn');
            let itemIndex = 1;

            function bindProduitSelect(row) {
                const produitSelect = row.querySelector('.produit-select');
                const condSelect = row.querySelector('.cond-select');
                const prixInput = row.querySelector('.prix-input');

                produitSelect.addEventListener('change', function() {
                    const produitId = this.value;
                    condSelect.innerHTML = '<option value="">Choisir conditionnement</option>';

                    if (!produitId) {
                        condSelect.disabled = true;
                        prixInput.value = 0;
                        return;
                    }

                    const produit = produitsData.find(p => p.id === produitId);
                    if (produit && produit.conditionnements) {
                        produit.conditionnements.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.dataset.prix = c.prix_vente || (produit.prix_vente * c.quantite_unite_base);
                            opt.textContent = `${c.nom} (${c.quantite_unite_base} ${produit.unite_base})`;
                            condSelect.appendChild(opt);
                        });
                        condSelect.disabled = false;
                        if (produit.conditionnements.length > 0) {
                            condSelect.selectedIndex = 1;
                            const selectedOption = condSelect.options[1];
                            prixInput.value = selectedOption.dataset.prix || 0;
                        }
                    }
                });

                condSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.prix) {
                        prixInput.value = selectedOption.dataset.prix;
                    }
                });
            }

            document.querySelectorAll('.item-row').forEach(bindProduitSelect);

            addBtn.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'item-row border rounded p-3 mb-3 bg-light-subtle';
                newRow.dataset.index = itemIndex;
                newRow.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-muted">Ligne #${itemIndex + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bi bi-x"></i> Supprimer</button>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Produit <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm produit-select" name="items[${itemIndex}][produit_id]" required>
                                <option value="">Sélectionner un produit</option>
                                ${produitsData.map(p => `<option value="${p.id}">${p.nom} (Stock: ${p.stock ? p.stock.quantite : 0} ${p.unite_base})</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm cond-select" name="items[${itemIndex}][conditionnement_id]" required disabled>
                                <option value="">Choisir produit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Qté Cond. <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm qte-input" type="number" name="items[${itemIndex}][quantite_conditionnement]" value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Prix Vente Unitaire (FCFA) <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm prix-input" type="number" step="0.01" name="items[${itemIndex}][prix]" value="0" required>
                        </div>
                    </div>
                `;

                container.appendChild(newRow);
                bindProduitSelect(newRow);

                newRow.querySelector('.remove-row-btn').addEventListener('click', function() {
                    newRow.remove();
                });

                itemIndex++;
            });
        });
    </script>
</x-app-layout>
