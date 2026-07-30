<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Paramètres</p>
                <h1 class="h3 mb-0">Modifier l'Utilisateur — {{ $user->name }}</h1>
            </div>
            <div class="heading-actions">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Retour à la liste
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="row g-3">
        <div class="col-12 col-xl-8">
            <form class="panel" method="POST" action="{{ route('parametres.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-pencil" aria-hidden="true"></i><span>Informations du Collaborateur</span></h2>
                        <p class="text-muted mb-0">Modifiez les informations du compte.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nom complet <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Adresse e-mail <span class="text-danger">*</span></label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control @error('telephone') is-invalid @enderror" id="telephone" type="tel" name="telephone" value="{{ old('telephone', $user->telephone) }}">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="role">Rôle <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">Choisir un rôle</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="adresse">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="3">{{ old('adresse', $user->adresse) }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('parametres.users.index') }}">Annuler</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="col-12 col-xl-4">
            {{-- Informations Utilisateur --}}
            <div class="panel mb-3">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-person-badge" aria-hidden="true"></i><span>Fiche Utilisateur</span></h2>
                <div class="text-center mb-3">
                    <img class="avatar-img avatar-xl rounded-circle" src="{{ asset('assets/images/avatar/avatar.jpg') }}" alt="{{ $user->name }}">
                    <h5 class="mt-2 mb-0">{{ $user->name }}</h5>
                    <span class="badge {{ $user->actif ? 'bg-success' : 'bg-secondary' }} mt-1">{{ $user->actif ? 'Actif' : 'Désactivé' }}</span>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">E-mail</span>
                        <span>{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Rôle</span>
                        <span>{{ $user->roles->first()?->name ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Inscrit le</span>
                        <span>{{ $user->created_at->format('d/m/Y') }}</span>
                    </li>
                </ul>
            </div>

            {{-- Actions rapides --}}
            <div class="panel">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-lightning" aria-hidden="true"></i><span>Actions Rapides</span></h2>
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('parametres.users.toggle-active', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ $user->actif ? 'btn-outline-warning' : 'btn-outline-success' }} w-100">
                            <i class="bi bi-{{ $user->actif ? 'pause-circle' : 'play-circle' }} me-2"></i>
                            {{ $user->actif ? 'Désactiver le Compte' : 'Activer le Compte' }}
                        </button>
                    </form>

                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                        <i class="bi bi-key me-2"></i>Réinitialiser le Mot de Passe
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Reset Password Modal --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('parametres.users.reset-password', $user) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordLabel"><i class="bi bi-key me-2"></i>Réinitialiser le mot de passe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Définir un nouveau mot de passe pour <strong>{{ $user->name }}</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label" for="new-password">Nouveau mot de passe</label>
                            <input class="form-control" id="new-password" type="password" name="password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="new-password-confirm">Confirmer le mot de passe</label>
                            <input class="form-control" id="new-password-confirm" type="password" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Réinitialiser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
