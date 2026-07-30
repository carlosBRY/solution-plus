<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Achat & Stock</p>
                <h1 class="h3 mb-0">Nouvel Approvisionnement</h1>
            </div>
            <div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('approvisionnements.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('approvisionnements.store') }}" id="appForm">
        @csrf
        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="panel mb-3">
                    <div class="panel-header">
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-box-seam me-2"></i>Produits & Conditionnements à Approvisionner</h2>
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
                                                {{ $prod->nom }} (Stock actuel: {{ $prod->stock?->quantite ?? 0 }} {{ $prod->unite_base }})
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
                                    <label class="form-label small fw-semibold">Prix Achat Unitaire (FCFA) <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm prix-input" type="number" step="0.01" name="items[0][prix_achat]" value="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter une autre ligne
                    </button>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel">
                    <h2 class="h5 mb-3 section-title"><i class="bi bi-building me-2"></i>Entête Commande / Facture</h2>

                    <div class="mb-3">
                        <label class="form-label" for="fournisseur_id">Fournisseur <span class="text-danger">*</span></label>
                        <select class="form-select" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionner un fournisseur</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}">{{ $f->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="date">Date d'approvisionnement</label>
                        <input class="form-control" type="datetime-local" id="date" name="date" value="{{ date('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="statut">Statut Réception <span class="text-danger">*</span></label>
                        <select class="form-select" id="statut" name="statut" required>
                            <option value="RECEPTIONNE" selected>RECEPTIONNE (Incrémenter le stock immédiatement)</option>
                            <option value="EN_ATTENTE">EN ATTENTE (Stock non incrémenté)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="remise">Remise globale (FCFA)</label>
                            <input class="form-control" type="number" step="0.01" id="remise" name="remise" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="tva">TVA globale (FCFA)</label>
                            <input class="form-control" type="number" step="0.01" id="tva" name="tva" value="0">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Enregistrer l'Approvisionnement</button>
                        <a href="{{ route('approvisionnements.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
                            opt.dataset.prix = c.prix_achat || (produit.prix_achat * c.quantite_unite_base);
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
                            <label class="form-label small fw-semibold">Prix Achat Unitaire (FCFA) <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm prix-input" type="number" step="0.01" name="items[${itemIndex}][prix_achat]" value="0" required>
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
