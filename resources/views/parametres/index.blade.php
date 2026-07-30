<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Administration</p>
                <h1 class="h3 mb-0">Paramètres Général de la Cave</h1>
            </div>
        </div>
    </x-slot>

    {{-- Sub-navigation Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('parametres.index') }}">
                <i class="bi bi-gear me-1"></i> Général & Entreprise
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.conditionnements') }}">
                <i class="bi bi-box-seam me-1"></i> Conditionnements des Produits
            </a>
        </li>
        @can('gérer-utilisateurs')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-people me-1"></i> Gestion des Utilisateurs
                </a>
            </li>
        @endcan
    </ul>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="row g-3">
        <div class="col-12 col-xl-8">
            <form class="panel" method="POST" action="{{ route('parametres.update-general') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-building me-2"></i>Informations de l'Établissement</h2>
                        <p class="text-muted mb-0">Configurez l'identité de votre cave, la devise et la facturation.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nom_cave">Nom de la Cave <span class="text-danger">*</span></label>
                        <input class="form-control @error('nom_cave') is-invalid @enderror" id="nom_cave" type="text" name="nom_cave" value="{{ old('nom_cave', $parametre->nom_cave) }}" required>
                        @error('nom_cave')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Adresse E-mail</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $parametre->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control @error('telephone') is-invalid @enderror" id="telephone" type="tel" name="telephone" value="{{ old('telephone', $parametre->telephone) }}">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="devise">Devise Monétaire <span class="text-danger">*</span></label>
                        <input class="form-control @error('devise') is-invalid @enderror" id="devise" type="text" name="devise" value="{{ old('devise', $parametre->devise) }}" required>
                        @error('devise')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="tva">Taux de TVA (%) <span class="text-danger">*</span></label>
                        <input class="form-control @error('tva') is-invalid @enderror" id="tva" type="number" step="0.01" name="tva" value="{{ old('tva', $parametre->tva) }}" required>
                        @error('tva')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="stock_min_global">Seuil d'Alerte Stock Global <span class="text-danger">*</span></label>
                        <input class="form-control @error('stock_min_global') is-invalid @enderror" id="stock_min_global" type="number" name="stock_min_global" value="{{ old('stock_min_global', $parametre->stock_min_global) }}" required>
                        @error('stock_min_global')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="adresse">Adresse Physique</label>
                        <input class="form-control @error('adresse') is-invalid @enderror" id="adresse" type="text" name="adresse" value="{{ old('adresse', $parametre->adresse) }}">
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="message_ticket">Message de bas de Ticket de Caisse</label>
                        <textarea class="form-control @error('message_ticket') is-invalid @enderror" id="message_ticket" name="message_ticket" rows="3" placeholder="Message imprimé sur les reçus de vente...">{{ old('message_ticket', $parametre->message_ticket) }}</textarea>
                        @error('message_ticket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="logo">Logo Officiel de la Cave</label>
                        @if($parametre->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $parametre->logo) }}" alt="Logo Cave" class="img-thumbnail" style="max-height: 80px;">
                            </div>
                        @endif
                        <input class="form-control @error('logo') is-invalid @enderror" id="logo" type="file" name="logo" accept="image/*">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-circle me-1"></i> Enregistrer les Paramètres
                    </button>
                </div>
            </form>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel mb-3">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-activity me-2"></i>Aperçu de la Cave</h2>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Produits au catalogue</span>
                        <span class="fw-bold">{{ $totalProduits }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Conditionnements configurés</span>
                        <span class="fw-bold">{{ $totalConditionnements }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Devise active</span>
                        <span class="badge bg-primary-subtle text-primary">{{ $parametre->devise }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Taux TVA appliqué</span>
                        <span>{{ $parametre->tva }}%</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</x-app-layout>
