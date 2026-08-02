<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Gestion du Stock</p>
                <h1 class="h3 mb-0">Détérioration {{ $deterioration->numero }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('deteriorations.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
                @if($deterioration->isEstBrouillon())
                    <form method="POST" action="{{ route('deteriorations.valider', $deterioration) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Valider cette détérioration et décrémenter le stock ? Cette action est irréversible.')">
                            <i class="bi bi-check-circle me-1"></i> Valider & Décrémenter le Stock
                        </button>
                    </form>
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

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-list-check me-2"></i>Lignes de la Déclaration</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Conditionnement Utilisé</th>
                                <th>Quantité Saisie</th>
                                <th>Équivalent en Stock</th>
                                <th>Cause</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deterioration->details as $detail)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $detail->produit->nom }}</div>
                                        <small class="text-muted">Unité de base: {{ $detail->produit->unite_base }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-body border">{{ $detail->conditionnement->nom }}</span></td>
                                    <td class="fw-bold">{{ $detail->quantite_conditionnement }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $detail->quantite_unite_base }} {{ $detail->produit->unite_base }}(s)
                                        </span>
                                        <div class="small text-muted">(x{{ $detail->coefficient_conversion }})</div>
                                    </td>
                                    <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $detail->cause->value }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-info-circle me-2"></i>Détails du Dossier</h2>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Numéro</span>
                        <span class="fw-bold">{{ $deterioration->numero }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Statut</span>
                        @if($deterioration->isEstValidee())
                            <span class="badge bg-success">VALIDÉE</span>
                        @else
                            <span class="badge bg-warning text-dark">BROUILLON</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Saisi par</span>
                        <span>{{ $deterioration->user->name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Date de constatation</span>
                        <span>{{ $deterioration->date->format('d/m/Y H:i') }}</span>
                    </li>
                    @if($deterioration->isEstValidee())
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Validé par</span>
                            <span>{{ $deterioration->validateur?->name ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Date de validation</span>
                            <span>{{ $deterioration->date_validation?->format('d/m/Y H:i') ?? '—' }}</span>
                        </li>
                    @endif
                </ul>

                @if($deterioration->observation)
                    <div class="mt-3">
                        <label class="form-label text-muted small fw-semibold">Observation :</label>
                        <div class="p-2 bg-light rounded small">{{ $deterioration->observation }}</div>
                    </div>
                @endif

                @if($deterioration->isEstValidee())
                    <div class="alert alert-info mt-4 mb-0 small">
                        <i class="bi bi-lock me-1"></i> Cette détérioration a été validée. Le stock physique a été décrémenté et le mouvement est verrouillé.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
