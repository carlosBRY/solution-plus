<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Fiche Fournisseur</p>
                <h1 class="h3 mb-0">{{ $fournisseur->nom }}</h1>
            </div>
            <div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('fournisseurs.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
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

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="panel">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-building me-2"></i>Informations du Partenaire</h2>
                <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Nom / Maison</span>
                        <span class="fw-bold">{{ $fournisseur->nom }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Téléphone</span>
                        <span>{{ $fournisseur->telephone ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">E-mail</span>
                        <span>{{ $fournisseur->email ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Ville / Pays</span>
                        <span>{{ $fournisseur->ville ? $fournisseur->ville . ($fournisseur->pays ? ', ' . $fournisseur->pays : '') : '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Adresse</span>
                        <span>{{ $fournisseur->adresse ?? '—' }}</span>
                    </li>
                </ul>

                <h2 class="h5 mb-3 section-title"><i class="bi bi-truck me-2"></i>Volume d'Approvisionnement</h2>
                <div class="p-3 rounded border bg-light">
                    <span class="small text-muted fw-semibold d-block">Montant Total Approvisionné</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">
                        {{ number_format($totalApprovisionnementsVal, 0, ',', ' ') }} FCFA
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-box-seam me-2"></i>Derniers Approvisionnements Livrés</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Saisi par</th>
                                <th>Total Achat</th>
                                <th>Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fournisseur->approvisionnements as $app)
                                <tr>
                                    <td><a href="{{ route('approvisionnements.show', $app) }}" class="fw-bold text-decoration-none">{{ $app->numero }}</a></td>
                                    <td>{{ $app->date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $app->user->name }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($app->total, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @if($app->statut->value === 'RECEPTIONNE' || $app->statut === \App\Enums\StatutApprovisionnement::RECEPTIONNE)
                                            <span class="badge bg-success">Réceptionné</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('approvisionnements.show', $app) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Aucun approvisionnement enregistré pour ce fournisseur.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
