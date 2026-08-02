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

    {{-- Flash Messages & Errors --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>
            <strong>Impossible de valider la vente :</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('ventes.store') }}" id="venteForm">
        @csrf
        <div class="row g-3">
            {{-- Panier & Sélection Produits --}}
            <div class="col-12 col-xl-8">
                <div class="panel mb-3">
                    <div class="panel-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-cart3 me-2"></i>Panier d'Achat</h2>
                        <small class="text-muted"><i class="bi bi-lock me-1"></i>Prix de vente fixes paramétrés</small>
                    </div>

                    <div id="itemsContainer">
                        <div class="item-row border rounded p-3 mb-3 bg-light-subtle" data-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-primary"><i class="bi bi-cup-straw me-1"></i>Boisson #1</span>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-3">
                                    <label class="form-label small fw-semibold">Boisson <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm produit-select" name="items[0][produit_id]" required>
                                        <option value="">Sélectionner une boisson</option>
                                        @foreach($produits as $prod)
                                            @php
                                                $qte = $prod->stock?->quantite ?? 0;
                                                $isSousStockMin = $qte <= $prod->stock_min;
                                                $labelStock = "Stock: {$prod->stock_formate}";
                                                if ($isSousStockMin) {
                                                    $labelStock .= ' - ⚠️ STOCK MINIMUM';
                                                }
                                            @endphp
                                            <option value="{{ $prod->id }}" data-stock="{{ $qte }}" data-stock-min="{{ $prod->stock_min }}">
                                                {{ $prod->nom }} ({{ $labelStock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm cond-select ts-ignore" name="items[0][conditionnement_id]" required disabled>
                                        <option value="">Choisir boisson</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small fw-semibold">Qté <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm qte-input text-center fw-bold" type="number" name="items[0][quantite_conditionnement]" value="1" min="1" required>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small fw-semibold">Prix Unit. (FCFA)</label>
                                    <input class="form-control form-control-sm prix-input bg-light text-end fw-semibold" type="number" step="0.01" name="items[0][prix]" value="0" readonly title="Prix fixe paramétré">
                                </div>
                                <div class="col-12 col-md-2 text-end">
                                    <label class="form-label small fw-bold text-success d-block">Total Ligne</label>
                                    <span class="total-ligne-val fw-extrabold text-success fs-6">0 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter une autre boisson
                        </button>
                        <div class="text-end">
                            <span class="text-muted small d-block">TOTAL GÉNÉRAL DU PANIER :</span>
                            <span class="fw-extrabold fs-4 text-primary" id="lblPanierTotal">0 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panneau Encaissement & Règlement --}}
            <div class="col-12 col-xl-4">
                <div class="panel">
                    <h2 class="h5 mb-3 section-title"><i class="bi bi-cash-coin me-2"></i>Encaissement & Règlement</h2>

                    <div class="mb-3">
                        <label class="form-label" for="client_id">
                            Client <span id="clientRequiredBadge" class="text-danger fw-bold d-none">* Obligatoire pour Crédit</span>
                        </label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">Client Comptant (Passant)</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }} {{ $c->prenom }} ({{ $c->telephone }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Informations sur le reçu pour Client Comptant (Passant) --}}
                    <div class="card border bg-light-subtle p-2 mb-3" id="boxClientComptant">
                        <div class="fw-semibold small text-muted mb-2">
                            <i class="bi bi-person-badge me-1"></i> Infos Client pour le Reçu (Optionnel)
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1 text-muted" for="client_comptant_nom">Nom</label>
                                <input class="form-control form-control-sm" type="text" id="client_comptant_nom" name="client_comptant_nom" value="{{ old('client_comptant_nom') }}" placeholder="Ex: Kouassi">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1 text-muted" for="client_comptant_prenom">Prénom(s)</label>
                                <input class="form-control form-control-sm" type="text" id="client_comptant_prenom" name="client_comptant_prenom" value="{{ old('client_comptant_prenom') }}" placeholder="Ex: Koffi">
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-1 text-muted" for="client_comptant_contact">Téléphone / Contact</label>
                                <input class="form-control form-control-sm" type="text" id="client_comptant_contact" name="client_comptant_contact" value="{{ old('client_comptant_contact') }}" placeholder="Ex: 07 01 02 03 04">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="mode_paiement">Mode de Règlement <span class="text-danger">*</span></label>
                        <select class="form-select fw-bold" id="mode_paiement" name="mode_paiement" required>
                            @foreach($modesPaiement as $m)
                                <option value="{{ $m->value }}">{{ $m->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="remise_globale">Remise Globale (FCFA)</label>
                            <input class="form-control" type="number" step="0.01" id="remise_globale" name="remise_globale" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="montant_paye">Montant Remis / Reçu (FCFA) <span class="text-danger">*</span></label>
                            <input class="form-control fw-bold fs-5 text-success" type="number" step="0.01" id="montant_paye" name="montant_paye" value="0" min="0" required placeholder="Ex: 10000">
                        </div>
                    </div>

                    {{-- Live Calculation Summary Box --}}
                    <div class="card bg-light border-0 mb-3 p-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Sous-total Panier :</span>
                            <span class="fw-semibold" id="lblSousTotal">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Remise Globale :</span>
                            <span class="fw-semibold text-danger" id="lblRemise">0 FCFA</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold mb-2">
                            <span>TOTAL NET À PAYER :</span>
                            <span class="text-primary fs-5" id="lblTotalNet">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1" id="rowMontantRemis">
                            <span class="text-muted">Montant Remis par Client :</span>
                            <span class="fw-bold text-dark" id="lblMontantRemisVal">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1" id="rowMonnaie">
                            <span class="text-muted" id="lblMonnaieTitre">Monnaie à Rendre :</span>
                            <span class="fw-bold text-success" id="lblMonnaieVal">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top fw-bold" id="rowNetCaisse">
                            <span class="text-dark"><i class="bi bi-wallet2 me-1 text-success"></i>Entrée Net en Caisse :</span>
                            <span class="fw-bold text-success fs-6" id="lblNetCaisseVal">0 FCFA</span>
                        </div>
                    </div>

                    {{-- Dynamic Discrepancy / Credit Warning Alert --}}
                    <div class="alert alert-warning border-warning align-items-center mb-3 p-2 small d-none" id="alertSousPaiement" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                        <div>
                            <strong>Attention :</strong> Le montant reçu est inférieur au total net. Pour accorder un crédit, choisissez le mode <strong>Crédit</strong> et sélectionnez un client.
                        </div>
                    </div>

                    {{-- Confirmation Administrateur pour Vente sous Stock Minimum --}}
                    @if($isAdmin ?? false)
                        <div class="card border-warning bg-warning bg-opacity-10 mb-3 p-3" id="boxConfirmationAdmin">
                            <div class="form-check">
                                <input class="form-check-input @error('confirmer_vente_stock_min') is-invalid @enderror" type="checkbox" name="confirmer_vente_stock_min" value="1" id="confirmer_vente_stock_min" {{ old('confirmer_vente_stock_min') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="confirmer_vente_stock_min">
                                    <i class="bi bi-shield-lock-fill text-warning me-1"></i> Autorisation Administrateur : Je confirme vouloir débloquer la vente de produit(s) sous le stock minimum.
                                </label>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Obligatoire uniquement si le panier contient une boisson sous le seuil d'alerte.</small>
                        </div>
                    @endif

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg fw-bold" id="btnSubmitVente">
                            <i class="bi bi-check2-circle me-1"></i> Valider & Imprimer Reçu
                        </button>
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
            const modeSelect = document.getElementById('mode_paiement');
            const clientSelect = document.getElementById('client_id');
            const clientBadge = document.getElementById('clientRequiredBadge');
            const remiseInput = document.getElementById('remise_globale');
            const payeInput = document.getElementById('montant_paye');
            const alertSousPaiement = document.getElementById('alertSousPaiement');

            let itemIndex = 1;

            function updateCalculs() {
                let sousTotal = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const qte = parseFloat(row.querySelector('.qte-input')?.value) || 0;
                    const prix = parseFloat(row.querySelector('.prix-input')?.value) || 0;
                    const totalLigne = qte * prix;
                    sousTotal += totalLigne;

                    const lblTotalLigne = row.querySelector('.total-ligne-val');
                    if (lblTotalLigne) {
                        lblTotalLigne.textContent = totalLigne.toLocaleString('fr-FR') + ' FCFA';
                    }
                });

                const remise = parseFloat(remiseInput.value) || 0;
                const totalNet = Math.max(0, sousTotal - remise);
                const paye = parseFloat(payeInput.value) || 0;
                const isCredit = modeSelect.value === 'CREDIT' || modeSelect.value === 'Crédit';

                const strSousTotal = sousTotal.toLocaleString('fr-FR') + ' FCFA';
                const strRemise = remise.toLocaleString('fr-FR') + ' FCFA';
                const strTotalNet = totalNet.toLocaleString('fr-FR') + ' FCFA';

                document.getElementById('lblSousTotal').textContent = strSousTotal;
                document.getElementById('lblRemise').textContent = strRemise;
                document.getElementById('lblTotalNet').textContent = strTotalNet;
                document.getElementById('lblPanierTotal').textContent = strTotalNet;

                const monnaie = Math.max(0, paye - totalNet);
                const netCaisse = Math.min(paye, totalNet);

                document.getElementById('lblMontantRemisVal').textContent = paye.toLocaleString('fr-FR') + ' FCFA';
                document.getElementById('lblNetCaisseVal').textContent = (isCredit ? Math.min(paye, totalNet) : netCaisse).toLocaleString('fr-FR') + ' FCFA';

                if (isCredit) {
                    const soldeRestant = Math.max(0, totalNet - paye);
                    document.getElementById('lblMonnaieTitre').textContent = 'Solde à ajouter en Dette :';
                    document.getElementById('lblMonnaieVal').textContent = soldeRestant.toLocaleString('fr-FR') + ' FCFA';
                    document.getElementById('lblMonnaieVal').className = 'fw-bold text-danger';
                    alertSousPaiement.classList.add('d-none');
                } else {
                    document.getElementById('lblMonnaieTitre').textContent = 'Monnaie à Rendre :';
                    if (paye >= totalNet) {
                        document.getElementById('lblMonnaieVal').textContent = monnaie.toLocaleString('fr-FR') + ' FCFA';
                        document.getElementById('lblMonnaieVal').className = 'fw-bold text-success';
                        alertSousPaiement.classList.add('d-none');
                    } else {
                        const manque = totalNet - paye;
                        document.getElementById('lblMonnaieVal').textContent = 'Manque ' + manque.toLocaleString('fr-FR') + ' FCFA';
                        document.getElementById('lblMonnaieVal').className = 'fw-bold text-danger';
                        if (sousTotal > 0) {
                            alertSousPaiement.classList.remove('d-none');
                        }
                    }
                }
            }

            function handleModePaiementChange() {
                const isCredit = modeSelect.value === 'CREDIT' || modeSelect.value === 'Crédit';
                if (isCredit) {
                    clientSelect.required = true;
                    clientBadge.classList.remove('d-none');
                    if (payeInput.value == 0 || payeInput.dataset.autoSet === 'true') {
                        payeInput.value = 0;
                        payeInput.dataset.autoSet = 'true';
                    }
                } else {
                    clientSelect.required = false;
                    clientBadge.classList.add('d-none');
                }
                updateCalculs();
            }

            function toggleClientComptantBox() {
                const boxClientComptant = document.getElementById('boxClientComptant');
                if (boxClientComptant) {
                    if (clientSelect.value === '') {
                        boxClientComptant.classList.remove('d-none');
                    } else {
                        boxClientComptant.classList.add('d-none');
                    }
                }
            }

            modeSelect.addEventListener('change', handleModePaiementChange);
            clientSelect.addEventListener('change', toggleClientComptantBox);
            remiseInput.addEventListener('input', updateCalculs);
            payeInput.addEventListener('input', function() {
                payeInput.dataset.autoSet = 'false';
                updateCalculs();
            });

            toggleClientComptantBox();

            function getProductOptionLabel(p) {
                const qte = p.stock ? p.stock.quantite : 0;
                let label = `Stock: ${qte} ${p.unite_base}`;
                if (qte <= p.stock_min) {
                    label += ' - ⚠️ STOCK MINIMUM';
                }
                return `${p.nom} (${label})`;
            }

            function bindProduitSelect(row) {
                const produitSelect = row.querySelector('.produit-select');
                const condSelect = row.querySelector('.cond-select');
                const prixInput = row.querySelector('.prix-input');
                const qteInput = row.querySelector('.qte-input');

                produitSelect.addEventListener('change', function() {
                    const produitId = this.value;

                    if (condSelect.tomselect) {
                        condSelect.tomselect.destroy();
                    }

                    condSelect.innerHTML = '<option value="">Choisir conditionnement</option>';

                    if (!produitId) {
                        condSelect.disabled = true;
                        prixInput.value = 0;
                        updateCalculs();
                        return;
                    }

                    const produit = produitsData.find(p => String(p.id) === String(produitId));
                    if (produit) {
                        const conds = (produit.conditionnements && produit.conditionnements.length > 0)
                            ? produit.conditionnements
                            : [{
                                id: '',
                                nom: produit.unite_base || 'Unité',
                                quantite_unite_base: 1,
                                prix_vente: produit.prix_vente || 0
                            }];

                        conds.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            const prixVente = parseFloat(c.prix_vente) || (parseFloat(produit.prix_vente) * parseFloat(c.quantite_unite_base)) || 0;
                            opt.dataset.prix = prixVente;
                            opt.textContent = `${c.nom} (${c.quantite_unite_base} ${produit.unite_base || ''}) - ${prixVente.toLocaleString('fr-FR')} FCFA`;
                            condSelect.appendChild(opt);
                        });

                        condSelect.disabled = false;
                        if (condSelect.options.length > 1) {
                            condSelect.selectedIndex = 1;
                            const selectedOption = condSelect.options[1];
                            prixInput.value = selectedOption.dataset.prix || 0;
                        }
                    }
                    updateCalculs();
                });

                condSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.prix) {
                        prixInput.value = selectedOption.dataset.prix;
                    } else {
                        prixInput.value = 0;
                    }
                    updateCalculs();
                });

                if (qteInput) {
                    qteInput.addEventListener('input', updateCalculs);
                }

                if (produitSelect.value) {
                    produitSelect.dispatchEvent(new Event('change'));
                }
            }

            document.querySelectorAll('.item-row').forEach(bindProduitSelect);

            addBtn.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'item-row border rounded p-3 mb-3 bg-light-subtle';
                newRow.dataset.index = itemIndex;
                newRow.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-primary"><i class="bi bi-cup-straw me-1"></i>Boisson #${itemIndex + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bi bi-x"></i> Supprimer</button>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold">Boisson <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm produit-select" name="items[${itemIndex}][produit_id]" required>
                                <option value="">Sélectionner une boisson</option>
                                ${produitsData.map(p => `<option value="${p.id}">${getProductOptionLabel(p)}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold">Conditionnement <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm cond-select ts-ignore" name="items[${itemIndex}][conditionnement_id]" required disabled>
                                <option value="">Choisir boisson</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Qté <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm qte-input text-center fw-bold" type="number" name="items[${itemIndex}][quantite_conditionnement]" value="1" min="1" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Prix Unit. (FCFA)</label>
                            <input class="form-control form-control-sm prix-input bg-light text-end fw-semibold" type="number" step="0.01" name="items[${itemIndex}][prix]" value="0" readonly title="Prix fixe paramétré">
                        </div>
                        <div class="col-12 col-md-2 text-end">
                            <label class="form-label small fw-bold text-success d-block">Total Ligne</label>
                            <span class="total-ligne-val fw-extrabold text-success fs-6">0 FCFA</span>
                        </div>
                    </div>
                `;

                container.appendChild(newRow);
                bindProduitSelect(newRow);

                newRow.querySelector('.remove-row-btn').addEventListener('click', function() {
                    newRow.remove();
                    updateCalculs();
                });

                itemIndex++;
                updateCalculs();
            });

            handleModePaiementChange();
        });
    </script>
</x-app-layout>
