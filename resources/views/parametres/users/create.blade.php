<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Paramètres</p>
                <h1 class="h3 mb-0">Créer un Utilisateur</h1>
            </div>
            <div class="heading-actions">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Retour à la liste
                </a>
            </div>
        </div>
    </x-slot>

    <section class="row g-3">
        <div class="col-12 col-xl-8">
            <form class="panel" method="POST" action="{{ route('parametres.users.store') }}">
                @csrf
                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-person-plus" aria-hidden="true"></i><span>Informations du Collaborateur</span></h2>
                        <p class="text-muted mb-0">Renseignez les informations du nouveau compte.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nom complet <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Adresse e-mail <span class="text-danger">*</span></label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control @error('telephone') is-invalid @enderror" id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="role">Rôle <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">Choisir un rôle</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Mot de passe <span class="text-danger">*</span></label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="adresse">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="3" placeholder="Adresse postale (optionnel)">{{ old('adresse') }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('parametres.users.index') }}">Annuler</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-person-check" aria-hidden="true"></i> Créer le Compte</button>
                </div>
            </form>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel h-100">
                <h2 class="h5 mb-3 section-title"><i class="bi bi-list-check" aria-hidden="true"></i><span>Guide de Création</span></h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <span class="activity-dot bg-success"></span>
                        <div>
                            <p class="mb-1 fw-semibold">Attribuer un rôle</p>
                            <p class="text-muted small mb-0">Le rôle détermine les permissions d'accès aux modules de la cave.</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <span class="activity-dot bg-primary"></span>
                        <div>
                            <p class="mb-1 fw-semibold">Mot de passe sécurisé</p>
                            <p class="text-muted small mb-0">Minimum 8 caractères. Le collaborateur pourra le modifier ultérieurement.</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <span class="activity-dot bg-warning"></span>
                        <div>
                            <p class="mb-1 fw-semibold">Désactivation possible</p>
                            <p class="text-muted small mb-0">Un compte peut être désactivé sans suppression définitive.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
