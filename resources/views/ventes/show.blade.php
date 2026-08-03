<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 invoice-actions-header">
            <div>
                <p class="eyebrow mb-1 text-muted">Document Commercial</p>
                <h1 class="h3 mb-0">Facture / Reçu N° {{ $vente->numero }}</h1>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('ventes.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Retour aux Ventes
                </a>
                <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer la Facture / Reçu
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 invoice-no-print" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Feuille / Facture Professionnelle Unifiée --}}
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border-0 shadow-sm invoice-paper p-4 p-md-5 bg-white">
                
                {{-- En-tête de la Facture --}}
                <div class="row pb-3 mb-3 border-bottom align-items-start print-row">
                    <div class="col-12 col-md-7 print-col-7 mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            @if($parametre->logo)
                                <img src="{{ asset('storage/' . $parametre->logo) }}" alt="{{ $parametre->nom_cave }}" style="max-height: 48px;" class="mb-1">
                            @else
                                <div class="badge bg-primary p-2 rounded fs-4 text-white">
                                    <i class="bi bi-shop"></i>
                                </div>
                            @endif
                            <div>
                                <h3 class="fw-bold mb-0 text-primary text-uppercase letter-spacing-1">{{ $parametre->nom_cave }}</h3>
                                <span class="small text-muted fw-semibold">Gestion Commerciale & Point de Vente</span>
                            </div>
                        </div>
                        <div class="small text-muted ms-1">
                            @if($parametre->adresse)
                                <div><i class="bi bi-geo-alt me-1 text-secondary"></i>{{ $parametre->adresse }}</div>
                            @endif
                            @if($parametre->telephone)
                                <div><i class="bi bi-telephone me-1 text-secondary"></i>Tél: {{ $parametre->telephone }}</div>
                            @endif
                            @if($parametre->email)
                                <div><i class="bi bi-envelope me-1 text-secondary"></i>Email: {{ $parametre->email }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-5 print-col-5 text-start text-md-end">
                        <div class="bg-light p-3 rounded border text-start text-md-end">
                            <h4 class="fw-extrabold text-dark mb-1">FACTURE / REÇU</h4>
                            <div class="fw-bold fs-5 text-primary mb-2">N° {{ $vente->numero }}</div>
                            <div class="small text-secondary mb-1">
                                <strong>Date :</strong> {{ $vente->date->format('d/m/Y H:i') }}
                            </div>
                            <div class="small text-secondary">
                                <strong>Caissier / Vendeur :</strong> {{ $vente->user->name }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informations Client & Statut --}}
                <div class="row mb-3 print-row">
                    <div class="col-12 col-md-7 print-col-7 mb-3 mb-md-0">
                        <div class="p-3 bg-light-subtle rounded border h-100">
                            <h6 class="text-uppercase small fw-bold text-muted mb-2">
                                <i class="bi bi-person me-1"></i>Facturé à (Client)
                            </h6>
                            <div class="fw-bold fs-6 text-dark">
                                @if($vente->client)
                                    {{ $vente->client->nom }} {{ $vente->client->prenom }}
                                @elseif($vente->client_comptant_nom || $vente->client_comptant_prenom)
                                    {{ trim($vente->client_comptant_nom . ' ' . $vente->client_comptant_prenom) }}
                                @else
                                    Client Comptant (Passant)
                                @endif
                            </div>
                            @if($vente->client?->telephone || $vente->client_comptant_contact)
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-telephone me-1"></i>Contact : {{ $vente->client?->telephone ?? $vente->client_comptant_contact }}
                                </div>
                            @endif
                            @if($vente->client?->adresse)
                                <div class="small text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>Adresse : {{ $vente->client->adresse }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-5 print-col-5">
                        <div class="p-3 bg-light-subtle rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="text-uppercase small fw-bold text-muted mb-2">
                                    <i class="bi bi-info-circle me-1"></i>Statut du Règlement
                                </h6>
                                <div>
                                    @if($vente->statut->value === 'PAYEE_CREDIT' || $vente->statut === \App\Enums\StatutVente::PAYEE_CREDIT)
                                        <span class="badge bg-info text-dark px-3 py-2 fs-6">
                                            <i class="bi bi-check-all me-1"></i>CRÉDIT RÉGLÉ
                                        </span>
                                    @elseif($vente->statut->value === 'PAYEE' || $vente->statut === \App\Enums\StatutVente::PAYEE)
                                        <span class="badge bg-success px-3 py-2 fs-6">
                                            <i class="bi bi-check-circle-fill me-1"></i>PAYÉE (COMPTANT)
                                        </span>
                                    @elseif($vente->statut->value === 'EN_ATTENTE' || $vente->statut === \App\Enums\StatutVente::EN_ATTENTE)
                                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                            <i class="bi bi-clock-history me-1"></i>CRÉDIT EN ATTENTE
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2 fs-6">
                                            ANNULÉE
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="small text-muted mt-2">
                                Mode(s) : 
                                @foreach($vente->paiements as $p)
                                    <span class="badge bg-light text-dark border">{{ $p->mode }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tableau des Articles --}}
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle text-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Désignation Produit</th>
                                <th>Conditionnement</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Prix Unit.</th>
                                <th class="text-end">Remise</th>
                                <th class="text-end">Total Ligne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vente->details as $index => $detail)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $detail->produit->nom }}</div>
                                        <small class="text-muted">{{ $detail->produit->unite_base }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-body border fw-normal">
                                            {{ $detail->conditionnement?->nom ?? 'Unité' }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold fs-6">
                                        {{ $detail->quantite_conditionnement ?? $detail->quantite }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        {{ number_format($detail->prix, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $detail->remise > 0 ? number_format($detail->remise, 0, ',', ' ') . ' FCFA' : '—' }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ number_format($detail->total, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Décomposition Financière & Encaissement Caisse --}}
                <div class="row mb-3 print-row">
                    <div class="col-12 col-md-6 print-col-6 mb-3 mb-md-0">
                        <div class="p-3 border rounded bg-light-subtle h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold small text-muted text-uppercase mb-2">
                                    <i class="bi bi-journal-text me-1"></i>Notes & Conditions
                                </h6>
                                <p class="small text-muted mb-2">
                                    {{ $parametre->message_ticket ?? 'Merci de votre confiance et de votre fidélité !' }}
                                </p>
                            </div>
                            <div class="small text-secondary fst-italic border-top pt-2 mt-2">
                                <i class="bi bi-shield-exclamation me-1"></i>Les marchandises vendues ne sont ni reprises ni échangées.
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 print-col-6">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-end text-muted fw-semibold">Sous-total :</td>
                                        <td class="text-end fw-bold" style="width: 140px;">{{ number_format($vente->sous_total, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    @if($vente->remise > 0)
                                        <tr>
                                            <td class="text-end text-muted">Remise globale :</td>
                                            <td class="text-end text-danger fw-bold">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endif
                                    @if($vente->tva > 0)
                                        <tr>
                                            <td class="text-end text-muted">TVA :</td>
                                            <td class="text-end text-dark fw-bold">+{{ number_format($vente->tva, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endif
                                    <tr class="border-top border-2">
                                        <td class="text-end fw-extrabold fs-5 text-dark">TOTAL NET À PAYER :</td>
                                        <td class="text-end fw-extrabold fs-5 text-primary">{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td class="text-end fw-semibold text-secondary">Montant Remis par Client :</td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    @if($vente->monnaie > 0)
                                        <tr class="bg-light">
                                            <td class="text-end fw-semibold text-secondary">Monnaie Rendue au Client :</td>
                                            <td class="text-end fw-bold text-danger">-{{ number_format($vente->monnaie, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endif
                                    <tr class="table-success border-top border-success">
                                        <td class="text-end fw-bold text-success fs-6">
                                            <i class="bi bi-wallet2 me-1"></i>Net Encaissé en Caisse :
                                        </td>
                                        <td class="text-end fw-extrabold text-success fs-6">
                                            {{ number_format($vente->montant_encaisse, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Signatures & Cachet --}}
                <div class="row pt-3 mt-3 border-top text-center d-none d-print-flex print-row invoice-signatures">
                    <div class="col-6 print-col-6">
                        <div class="small fw-bold text-muted mb-4">Signature & Cachet du Client</div>
                        <div class="border-bottom mx-auto" style="width: 180px;"></div>
                    </div>
                    <div class="col-6 print-col-6">
                        <div class="small fw-bold text-muted mb-4">Signature & Cachet de la Cave</div>
                        <div class="border-bottom mx-auto" style="width: 180px;"></div>
                    </div>
                </div>

                <div class="text-center pt-2 text-muted small border-top mt-3 invoice-footer-note">
                    <i class="bi bi-check-circle me-1"></i>Document officiel généré par <strong>{{ $parametre->nom_cave }}</strong> — Solution Plus
                </div>

            </div>
        </div>
    </div>

    {{-- Styles d'impression propres (@media print) --}}
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            /* Masquer l'ensemble des éléments de l'application web */
            .admin-sidebar,
            .sidebar-backdrop,
            .admin-navbar,
            .admin-footer,
            .invoice-actions-header,
            .invoice-no-print,
            .page-heading,
            nav,
            header,
            footer,
            button,
            .alert {
                display: none !important;
            }

            /* Réinitialiser les fonds et conteneurs pour une feuille A4 pure */
            body, html, .admin-shell, .admin-main, .dashboard-content, .container-fluid {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                height: auto !important;
            }

            .invoice-paper {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                font-size: 11.5px !important;
                line-height: 1.3 !important;
            }

            /* Conserver la mise en page en colonnes à l'impression */
            .print-row {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                width: 100% !important;
            }

            .print-col-7 {
                width: 58.333333% !important;
                flex: 0 0 58.333333% !important;
                max-width: 58.333333% !important;
            }

            .print-col-5 {
                width: 41.666667% !important;
                flex: 0 0 41.666667% !important;
                max-width: 41.666667% !important;
            }

            .print-col-6 {
                width: 50% !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            /* Réduction harmonieuse des tailles et espaces pour l'impression */
            .invoice-paper h3 { font-size: 1.25rem !important; }
            .invoice-paper h4 { font-size: 1.1rem !important; }
            .invoice-paper h6 { font-size: 0.85rem !important; }
            .invoice-paper .fs-5 { font-size: 1.05rem !important; }
            .invoice-paper .fs-6 { font-size: 0.9rem !important; }
            
            .invoice-paper .p-3 { padding: 0.5rem 0.75rem !important; }
            .invoice-paper .p-4, .invoice-paper .p-md-5 { padding: 0 !important; }
            .invoice-paper .mb-4 { margin-bottom: 0.75rem !important; }
            .invoice-paper .mb-3 { margin-bottom: 0.5rem !important; }
            .invoice-paper .pb-4 { padding-bottom: 0.5rem !important; }

            .invoice-paper table th, 
            .invoice-paper table td {
                padding: 4px 8px !important;
                font-size: 11px !important;
            }

            .invoice-signatures {
                display: flex !important;
                margin-top: 1rem !important;
                padding-top: 0.5rem !important;
            }

            .invoice-signatures .mb-4 {
                margin-bottom: 2.25rem !important;
            }

            .invoice-footer-note {
                margin-top: 0.75rem !important;
                padding-top: 0.5rem !important;
                font-size: 10px !important;
            }

            /* Éviter la coupure de page sur la facture */
            .invoice-paper, .print-row, table, .border {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Forcer le rendu exact des couleurs et bordures à l'impression */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</x-app-layout>
