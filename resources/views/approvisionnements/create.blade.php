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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Impossible d'enregistrer l'approvisionnement :</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Produit <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm produit-select" name="items[0][produit_id]" required>
                                        <option value="">Sélectionner un produit</option>
                                        @foreach($produits as $prod)
                                            @php
                                                $qte = $prod->stock?->quantite ?? 0;
                                                $labelStatut = '';
                                                if (!$prod->actif) {
                                                    $labelStatut = ' - [INACTIF]';
                                                } elseif ($qte <= $prod->stock_min) {
                                                    $labelStatut = ' - [⚠️ ALERTE STOCK BAS]';
                                                }
                                            @endphp
                                            <option value="{{ $prod->id }}">
                                                {{ $prod->nom }} (Stock: {{ $qte }} {{ $prod->unite_base }}){{ $labelStatut }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm cond-select ts-ignore" name="items[0][conditionnement_id]" required disabled>
                                        <option value="">Veuillez d'abord choisir un fournisseur</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Qté Cond. <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm qte-input" type="number" name="items[0][quantite_conditionnement]" value="1" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Prix Achat (FCFA)</label>
                                    <input class="form-control form-control-sm prix-input bg-light" type="number" step="0.01" name="items[0][prix_achat]" value="0" readonly required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Total Ligne</label>
                                    <input class="form-control form-control-sm total-ligne-input bg-light fw-bold text-end" type="text" value="0 FCFA" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top bg-light p-3 rounded">
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                                <i class="bi bi-plus-circle me-1"></i> Ajouter une autre ligne
                            </button>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Sous-total des lignes : <span id="sousTotalDisplay" class="fw-bold text-dark">0 FCFA</span></div>
                            <div class="h5 mb-0 fw-bold text-primary mt-1">Total Général : <span id="totalGeneralDisplay">0 FCFA</span></div>
                        </div>
                    </div>
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
                        <label class="form-label fw-bold" for="reference_facture">N° / Référence de la Facture <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="reference_facture" name="reference_facture" placeholder="Ex: FACT-2026-0489, N° BL..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="compte_financier_id">Moyen de Paiement / Compte à Débiter</label>
                        <select class="form-select @error('compte_financier_id') is-invalid @enderror" id="compte_financier_id" name="compte_financier_id">
                            <option value="">Sélectionner le compte de paiement...</option>
                            @foreach($comptes as $c)
                                <option value="{{ $c->id }}" data-solde="{{ $c->solde_courant }}">
                                    {{ $c->nom }} (Solde: {{ number_format($c->solde_courant, 0, ',', ' ') }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        <div id="soldeAlerte" class="alert alert-danger mt-2 py-2 px-3 small d-none">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> <span id="soldeAlerteTexte"></span>
                        </div>
                        <small class="text-muted">Le montant total de l'approvisionnement sera immédiatement débité de ce compte.</small>
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
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="bi bi-check-circle me-1"></i> Enregistrer l'Approvisionnement</button>
                        <a href="{{ route('approvisionnements.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produitsData = @json($produits);
            const tarifsFournisseurs = @json($tarifsFournisseurs ?? []);
            const container = document.getElementById('itemsContainer');
            const addBtn = document.getElementById('addItemBtn');
            const fournisseurSelect = document.getElementById('fournisseur_id');
            const remiseInput = document.getElementById('remise');
            const tvaInput = document.getElementById('tva');
            const compteSelect = document.getElementById('compte_financier_id');
            const soldeAlerte = document.getElementById('soldeAlerte');
            const soldeAlerteTexte = document.getElementById('soldeAlerteTexte');
            const sousTotalDisplay = document.getElementById('sousTotalDisplay');
            const totalGeneralDisplay = document.getElementById('totalGeneralDisplay');
            const submitBtn = document.getElementById('submitBtn');
            let itemIndex = 1;

            function formatFCFA(amount) {
                return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
            }

            function getConditionnementPrice(fournisseurId, conditionnement, produit) {
                if (fournisseurId && tarifsFournisseurs[fournisseurId] && tarifsFournisseurs[fournisseurId][conditionnement.id] !== undefined) {
                    return parseFloat(tarifsFournisseurs[fournisseurId][conditionnement.id]);
                }
                if (conditionnement.prix_achat && parseFloat(conditionnement.prix_achat) > 0) {
                    return parseFloat(conditionnement.prix_achat);
                }
                return (parseFloat(produit.prix_achat) || 0) * (parseFloat(conditionnement.quantite_unite_base) || 1);
            }

            function getCurrentTotalGeneral() {
                let sousTotal = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const qte = parseFloat(row.querySelector('.qte-input')?.value) || 0;
                    const prix = parseFloat(row.querySelector('.prix-input')?.value) || 0;
                    sousTotal += qte * prix;
                });
                const remise = parseFloat(remiseInput.value) || 0;
                const tva = parseFloat(tvaInput.value) || 0;
                return Math.max(0, sousTotal - remise + tva);
            }

            function checkSoldeCompte() {
                const selectedOpt = compteSelect.options[compteSelect.selectedIndex];
                if (!selectedOpt || !selectedOpt.value) {
                    soldeAlerte.classList.add('d-none');
                    submitBtn.disabled = false;
                    return true;
                }

                const solde = parseFloat(selectedOpt.dataset.solde) || 0;
                const totalGeneral = getCurrentTotalGeneral();

                if (totalGeneral > solde) {
                    const ecart = totalGeneral - solde;
                    soldeAlerteTexte.textContent = `Solde insuffisant sur ce compte ! Solde disponible : ${formatFCFA(solde)}, Total requis : ${formatFCFA(totalGeneral)} (Manque : ${formatFCFA(ecart)}).`;
                    soldeAlerte.classList.remove('d-none');
                    return false;
                } else {
                    soldeAlerte.classList.add('d-none');
                    return true;
                }
            }

            function recalculateTotals() {
                let sousTotal = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const qteInput = row.querySelector('.qte-input');
                    const prixInput = row.querySelector('.prix-input');
                    const totalLigneInput = row.querySelector('.total-ligne-input');

                    const qte = parseFloat(qteInput.value) || 0;
                    const prix = parseFloat(prixInput.value) || 0;
                    const totalLigne = qte * prix;

                    if (totalLigneInput) {
                        totalLigneInput.value = formatFCFA(totalLigne);
                    }

                    sousTotal += totalLigne;
                });

                const remise = parseFloat(remiseInput.value) || 0;
                const tva = parseFloat(tvaInput.value) || 0;
                const totalGeneral = Math.max(0, sousTotal - remise + tva);

                sousTotalDisplay.textContent = formatFCFA(sousTotal);
                totalGeneralDisplay.textContent = formatFCFA(totalGeneral);

                checkSoldeCompte();
            }

            function updateRowPricesForProduit(row) {
                const produitSelect = row.querySelector('.produit-select');
                const condSelect = row.querySelector('.cond-select');
                const prixInput = row.querySelector('.prix-input');
                const fournisseurId = fournisseurSelect ? fournisseurSelect.value : null;

                const produitId = produitSelect.value;
                if (!produitId) {
                    condSelect.innerHTML = '<option value="">Choisir produit</option>';
                    condSelect.disabled = true;
                    prixInput.value = 0;
                    return;
                }

                const produit = produitsData.find(p => p.id === produitId);
                if (!produit || !produit.conditionnements) return;

                if (!fournisseurId) {
                    condSelect.innerHTML = '<option value="">Veuillez d\'abord choisir un fournisseur</option>';
                    condSelect.disabled = true;
                    prixInput.value = 0;
                    return;
                }

                const supplierTarifs = tarifsFournisseurs[fournisseurId] || {};

                // Filtrer uniquement les conditionnements dont le tarif est renseigné chez ce fournisseur
                const validConditionnements = produit.conditionnements.filter(c => {
                    return supplierTarifs[c.id] !== undefined && parseFloat(supplierTarifs[c.id]) > 0;
                });

                const selectedCondId = condSelect.value;

                if (validConditionnements.length === 0) {
                    condSelect.innerHTML = '<option value="">Aucun conditionnement valable pour ce produit (aucun tarif paramétré)</option>';
                    condSelect.disabled = true;
                    prixInput.value = 0;
                } else {
                    condSelect.disabled = false;
                    condSelect.innerHTML = '<option value="">Choisir conditionnement</option>';
                    validConditionnements.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        const computedPrix = parseFloat(supplierTarifs[c.id]);
                        opt.dataset.prix = computedPrix;
                        opt.textContent = `${c.nom} (${c.quantite_unite_base} ${produit.unite_base}) - ${formatFCFA(computedPrix)}`;
                        if (c.id === selectedCondId) {
                            opt.selected = true;
                        }
                        condSelect.appendChild(opt);
                    });

                    if (condSelect.selectedIndex > 0) {
                        const selectedOption = condSelect.options[condSelect.selectedIndex];
                        prixInput.value = selectedOption.dataset.prix || 0;
                    } else if (validConditionnements.length > 0) {
                        condSelect.selectedIndex = 1;
                        const selectedOption = condSelect.options[1];
                        prixInput.value = selectedOption.dataset.prix || 0;
                    }
                }
            }

            function bindProduitSelect(row) {
                const produitSelect = row.querySelector('.produit-select');
                const condSelect = row.querySelector('.cond-select');
                const prixInput = row.querySelector('.prix-input');
                const qteInput = row.querySelector('.qte-input');

                produitSelect.addEventListener('change', function() {
                    updateRowPricesForProduit(row);
                    recalculateTotals();
                });

                condSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.prix) {
                        prixInput.value = selectedOption.dataset.prix;
                    } else {
                        prixInput.value = 0;
                    }
                    recalculateTotals();
                });

                qteInput.addEventListener('input', recalculateTotals);
            }

            if (fournisseurSelect) {
                fournisseurSelect.addEventListener('change', function() {
                    document.querySelectorAll('.item-row').forEach(row => {
                        updateRowPricesForProduit(row);
                    });
                    recalculateTotals();
                });
            }

            document.querySelectorAll('.item-row').forEach(bindProduitSelect);
            remiseInput.addEventListener('input', recalculateTotals);
            tvaInput.addEventListener('input', recalculateTotals);
            compteSelect.addEventListener('change', checkSoldeCompte);

            document.getElementById('appForm').addEventListener('submit', function(e) {
                if (!checkSoldeCompte()) {
                    e.preventDefault();
                    alert("Impossible d'enregistrer l'approvisionnement : Le solde du compte de paiement sélectionné est insuffisant pour décaisser le montant total de cet approvisionnement.");
                }
            });

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
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Produit <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm produit-select" name="items[${itemIndex}][produit_id]" required>
                                <option value="">Sélectionner un produit</option>
                                ${produitsData.map(p => {
                                    const qte = p.stock ? p.stock.quantite : 0;
                                    let label = '';
                                    if (!p.actif) {
                                        label = ' - [INACTIF]';
                                    } else if (qte <= p.stock_min) {
                                        label = ' - [⚠️ ALERTE STOCK BAS]';
                                    }
                                    return `<option value="${p.id}">${p.nom} (Stock: ${qte} ${p.unite_base})${label}</option>`;
                                }).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm cond-select ts-ignore" name="items[${itemIndex}][conditionnement_id]" required disabled>
                                <option value="">Veuillez d'abord choisir un fournisseur</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Qté Cond. <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm qte-input" type="number" name="items[${itemIndex}][quantite_conditionnement]" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Prix Achat (FCFA)</label>
                            <input class="form-control form-control-sm prix-input bg-light" type="number" step="0.01" name="items[${itemIndex}][prix_achat]" value="0" readonly required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Total Ligne</label>
                            <input class="form-control form-control-sm total-ligne-input bg-light fw-bold text-end" type="text" value="0 FCFA" readonly>
                        </div>
                    </div>
                `;

                container.appendChild(newRow);
                bindProduitSelect(newRow);

                newRow.querySelector('.remove-row-btn').addEventListener('click', function() {
                    newRow.remove();
                    recalculateTotals();
                });

                itemIndex++;
                recalculateTotals();
            });
        });
    </script>
</x-app-layout>
