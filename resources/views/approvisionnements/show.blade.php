<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Achat & Stock</p>
                <h1 class="h3 mb-0">Approvisionnement {{ $approvisionnement->numero }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('approvisionnements.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
                @if($approvisionnement->statut->value === 'EN_ATTENTE' || $approvisionnement->statut === \App\Enums\StatutApprovisionnement::EN_ATTENTE)
                    <form method="POST" action="{{ route('approvisionnements.receptionner', $approvisionnement) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirmer la réception de la commande et créditer les stocks ?')">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Réceptionner les Marchandises
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

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-list-check me-2"></i>Détail des Articles Livrés</h2>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Conditionnement</th>
                                <th>Qté Cond.</th>
                                <th>Équivalent Stock</th>
                                <th>Prix Achat Unit.</th>
                                <th>Total Ligne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvisionnement->details as $detail)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $detail->produit->nom }}</div>
                                        <small class="text-muted">Unité de base: {{ $detail->produit->unite_base }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-body border">{{ $detail->conditionnement->nom }}</span></td>
                                    <td class="fw-bold">{{ $detail->quantite_conditionnement }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">
                                            +{{ $detail->quantite }} {{ $detail->produit->unite_base }}(s)
                                        </span>
                                        <div class="small text-muted">(x{{ $detail->coefficient_conversion }})</div>
                                    </td>
                                    <td>{{ number_format($detail->prix_achat, 0, ',', ' ') }} FCFA</td>
                                    <td class="fw-bold text-primary">{{ number_format($detail->total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Sous-total :</td>
                                <td class="fw-bold">{{ number_format($approvisionnement->montant, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @if($approvisionnement->remise > 0)
                                <tr>
                                    <td colspan="5" class="text-end text-muted">Remise :</td>
                                    <td class="text-danger">-{{ number_format($approvisionnement->remise, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                            @if($approvisionnement->tva > 0)
                                <tr>
                                    <td colspan="5" class="text-end text-muted">TVA :</td>
                                    <td>+{{ number_format($approvisionnement->tva, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end fw-bold fs-5">Total Général :</td>
                                <td class="fw-bold text-success fs-5">{{ number_format($approvisionnement->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-building me-2"></i>Informations du Bon</h2>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Fournisseur</span>
                        <span class="fw-bold">{{ $approvisionnement->fournisseur->nom }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Numéro Bon</span>
                        <span>{{ $approvisionnement->numero }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted fw-bold">Réf. Facture</span>
                        <span class="fw-bold text-dark">{{ $approvisionnement->reference_facture ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Statut</span>
                        @if($approvisionnement->statut->value === 'RECEPTIONNE' || $approvisionnement->statut === \App\Enums\StatutApprovisionnement::RECEPTIONNE)
                            <span class="badge bg-success">RÉCEPTIONNÉ</span>
                        @else
                            <span class="badge bg-warning text-dark">EN ATTENTE</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Compte Débité</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $approvisionnement->compteFinancier?->nom ?? ($approvisionnement->mode ?? 'Non débité') }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Saisi par</span>
                        <span>{{ $approvisionnement->user->name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Date commande</span>
                        <span>{{ $approvisionnement->date->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
