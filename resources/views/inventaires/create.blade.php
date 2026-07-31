<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Inventaire & Logistique</p>
                <h1 class="h3 mb-0">Saisie d'un Inventaire Physique</h1>
            </div>
            <a href="{{ route('inventaires.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Annuler
            </a>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
        <div>
            <strong>Instructions :</strong> Comptez physiquement chaque produit et saisissez la quantité réelle constatée.
            Une fois validé, les stocks seront automatiquement mis à jour et un mouvement d'ajustement sera généré pour chaque écart.
        </div>
    </div>

    <form method="POST" action="{{ route('inventaires.store') }}" id="formInventaire">
        @csrf

        {{-- Observation --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <label for="observation" class="form-label fw-medium">Observation générale <span class="text-muted small">(optionnel)</span></label>
                <textarea id="observation" name="observation" class="form-control" rows="2"
                          placeholder="Ex: Inventaire mensuel de fin juillet, présence du gérant…">{{ old('observation') }}</textarea>
            </div>
        </div>

        {{-- Search bar --}}
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input id="searchProduit" type="text" class="form-control"
                       placeholder="Filtrer les produits par nom ou référence…">
            </div>
        </div>

        {{-- Products Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-check me-2 text-primary"></i>Feuille de Comptage
                </h6>
                <span class="badge bg-secondary rounded-pill" id="badgeTotal">{{ $produits->count() }} produit(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableInventaire">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produit</th>
                            <th>Catégorie</th>
                            <th class="text-center">Stock Théorique</th>
                            <th class="text-center" style="min-width: 140px;">
                                Stock Physique Constaté <span class="text-danger">*</span>
                            </th>
                            <th class="text-center">Écart Estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produits as $index => $produit)
                            @php $stockTheorique = $produit->stock?->quantite ?? 0; @endphp
                            <tr class="produit-row" data-nom="{{ strtolower($produit->nom) }}" data-ref="{{ strtolower($produit->reference ?? '') }}">
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][produit_id]" value="{{ $produit->id }}">
                                    <div class="fw-semibold">{{ $produit->nom }}</div>
                                    @if($produit->reference)
                                        <small class="text-muted">Réf: {{ $produit->reference }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                        {{ $produit->categorie?->nom ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-medium theorique-val">{{ $stockTheorique }}</span>
                                    <small class="text-muted d-block">{{ $produit->unite_base }}</small>
                                </td>
                                <td class="text-center">
                                    <input type="number"
                                           name="items[{{ $index }}][stock_physique]"
                                           class="form-control form-control-sm text-center physique-input"
                                           min="0"
                                           required
                                           value="{{ old("items.{$index}.stock_physique", $stockTheorique) }}"
                                           data-theorique="{{ $stockTheorique }}"
                                           data-row="{{ $index }}">
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold ecart-val" id="ecart-{{ $index }}">0</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Tous les champs « Stock Physique » sont obligatoires.
                </div>
                <button type="submit" class="btn btn-success btn-lg" id="btnValider">
                    <i class="bi bi-check-circle me-2"></i>Valider l'Inventaire
                </button>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Calcul en temps réel des écarts
        document.querySelectorAll('.physique-input').forEach(function (input) {
            function updateEcart() {
                const theorique = parseInt(input.dataset.theorique, 10) || 0;
                const physique  = parseInt(input.value, 10) || 0;
                const ecart     = physique - theorique;
                const rowIndex  = input.dataset.row;
                const span      = document.getElementById('ecart-' + rowIndex);

                span.textContent = (ecart >= 0 ? '+' : '') + ecart;
                span.className = 'fw-bold ecart-val ' + (ecart < 0 ? 'text-danger' : (ecart > 0 ? 'text-success' : 'text-muted'));
            }
            updateEcart();
            input.addEventListener('input', updateEcart);
        });

        // Filtre de recherche
        const searchInput = document.getElementById('searchProduit');
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows  = document.querySelectorAll('.produit-row');
            let visible = 0;
            rows.forEach(function (row) {
                const nom = row.dataset.nom || '';
                const ref = row.dataset.ref || '';
                const match = nom.includes(query) || ref.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) { visible++; }
            });
            document.getElementById('badgeTotal').textContent = visible + ' produit(s)';
        });

        // Confirmation avant soumission
        document.getElementById('formInventaire').addEventListener('submit', function (e) {
            if (!confirm("Confirmer la validation de l'inventaire ? Les stocks seront immédiatement mis à jour.")) {
                e.preventDefault();
            }
        });
    });
    </script>
</x-app-layout>
