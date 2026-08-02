<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Gestion du Stock</p>
                <h1 class="h3 mb-0">Déclarer une Détérioration / Casse</h1>
            </div>
            <div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('deteriorations.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('deteriorations.store') }}" id="deteriorationForm">
        @csrf
        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="panel mb-3">
                    <div class="panel-header">
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-list-check me-2"></i>Produits & Conditionnements Endommagés</h2>
                    </div>

                    <div id="itemsContainer">
                        <div class="item-row border rounded p-3 mb-3 bg-light-subtle" data-index="0">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Produit <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm produit-select" name="items[0][produit_id]" required>
                                        <option value="">Sélectionner un produit</option>
                                        @foreach($produits as $prod)
                                            <option value="{{ $prod->id }}" data-stock="{{ $prod->stock?->quantite ?? 0 }}" data-unite="{{ $prod->unite_base }}">
                                                {{ $prod->nom }} (Stock: {{ $prod->stock?->quantite ?? 0 }} {{ $prod->unite_base }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm cond-select ts-ignore" name="items[0][conditionnement_id]" required disabled>
                                        <option value="">Choisir produit</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Quantité <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm" type="number" name="items[0][quantite_conditionnement]" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cause <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="items[0][cause]" required>
                                        @foreach($causes as $cause)
                                            <option value="{{ $cause->value }}">{{ $cause->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <input class="form-control form-control-sm" type="text" name="items[0][observation]" placeholder="Observation optionnelle (ex: Cassée au déchargement)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter un autre produit
                    </button>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel">
                    <h2 class="h5 mb-3 section-title"><i class="bi bi-info-circle me-2"></i>Informations Générales</h2>
                    <div class="mb-3">
                        <label class="form-label" for="date">Date de constatation</label>
                        <input class="form-control" type="datetime-local" id="date" name="date" value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="observation">Observation Générale</label>
                        <textarea class="form-control" id="observation" name="observation" rows="4" placeholder="Commentaires ou notes globales..."></textarea>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Enregistrer le Brouillon</button>
                        <a href="{{ route('deteriorations.index') }}" class="btn btn-outline-secondary">Annuler</a>
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

                produitSelect.addEventListener('change', function() {
                    const produitId = this.value;
                    condSelect.innerHTML = '<option value="">Choisir conditionnement</option>';

                    if (!produitId) {
                        condSelect.disabled = true;
                        return;
                    }

                    const produit = produitsData.find(p => p.id === produitId);
                    if (produit && produit.conditionnements && produit.conditionnements.length > 0) {
                        produit.conditionnements.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = `${c.nom} (${c.quantite_unite_base} ${produit.unite_base})`;
                            condSelect.appendChild(opt);
                        });
                        condSelect.disabled = false;
                    } else {
                        condSelect.innerHTML = '<option value="">Aucun conditionnement configuré</option>';
                        condSelect.disabled = true;
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
                            <select class="form-select form-select-sm cond-select ts-ignore" name="items[${itemIndex}][conditionnement_id]" required disabled>
                                <option value="">Choisir produit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Quantité <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm" type="number" name="items[${itemIndex}][quantite_conditionnement]" value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Cause <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="items[${itemIndex}][cause]" required>
                                @foreach($causes as $cause)
                                    <option value="{{ $cause->value }}">{{ $cause->value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <input class="form-control form-control-sm" type="text" name="items[${itemIndex}][observation]" placeholder="Observation optionnelle">
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
