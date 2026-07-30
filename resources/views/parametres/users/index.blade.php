<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Paramètres</p>
                <h1 class="h3 mb-0">Gestion des Utilisateurs</h1>
            </div>
            <div class="heading-actions">
                <a class="btn btn-primary btn-sm" href="{{ route('parametres.users.create') }}">
                    <i class="bi bi-person-plus" aria-hidden="true"></i> Nouvel Utilisateur
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Sub-navigation Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.index') }}">
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
                <a class="nav-link active" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-people me-1"></i> Gestion des Utilisateurs
                </a>
            </li>
        @endcan
    </ul>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <section class="row g-3 mb-4" aria-label="Résumé utilisateurs">
        <div class="col-12 col-sm-6 col-xl-4">
            <article class="metric-card metric-primary">
                <div class="metric-top">
                    <span class="metric-label">Total Utilisateurs</span>
                    <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $totalUsers }}</div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <article class="metric-card metric-success">
                <div class="metric-top">
                    <span class="metric-label">Actifs</span>
                    <span class="metric-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $activeUsers }}</div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <article class="metric-card metric-danger">
                <div class="metric-top">
                    <span class="metric-label">Désactivés</span>
                    <span class="metric-icon"><i class="bi bi-slash-circle" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $inactiveUsers }}</div>
            </article>
        </div>
    </section>

    {{-- Users Table Panel --}}
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Liste des Utilisateurs</span></h2>
                <p class="text-muted mb-0">Gérez les comptes, rôles et accès des collaborateurs.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="GET" action="{{ route('parametres.users.index') }}" class="d-flex gap-2">
                    <input class="form-control form-control-sm table-search" type="search" name="search" placeholder="Rechercher un utilisateur..." value="{{ request('search') }}" aria-label="Rechercher">
                    <select class="form-select form-select-sm" name="role" style="width: auto;">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" name="statut" style="width: auto;">
                        <option value="">Tout statut</option>
                        <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Désactivé</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Utilisateur</th>
                        <th scope="col">Rôle</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Inscrit le</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img class="avatar-img avatar-sm" src="{{ asset('assets/images/avatar/avatar.jpg') }}" alt="{{ $user->name }}">
                                    <div>
                                        <p class="fw-semibold mb-0">{{ $user->name }}</p>
                                        <p class="text-muted small mb-0">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $user->telephone ?? '—' }}</td>
                            <td>
                                @if($user->actif)
                                    <span class="badge text-bg-success">Actif</span>
                                @else
                                    <span class="badge text-bg-secondary">Désactivé</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('parametres.users.edit', $user) }}">
                                                <i class="bi bi-pencil me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('parametres.users.toggle-active', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $user->actif ? 'text-warning' : 'text-success' }}">
                                                    <i class="bi bi-{{ $user->actif ? 'pause-circle' : 'play-circle' }} me-2"></i>
                                                    {{ $user->actif ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resetPasswordModal-{{ $user->id }}">
                                                <i class="bi bi-key me-2"></i>Réinitialiser MDP
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                {{-- Reset Password Modal --}}
                                <div class="modal fade" id="resetPasswordModal-{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordLabel-{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('parametres.users.reset-password', $user) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="resetPasswordLabel-{{ $user->id }}">
                                                        <i class="bi bi-key me-2"></i>Réinitialiser le mot de passe
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted">Définir un nouveau mot de passe pour <strong>{{ $user->name }}</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="password-{{ $user->id }}">Nouveau mot de passe</label>
                                                        <input class="form-control" id="password-{{ $user->id }}" type="password" name="password" required minlength="8">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="password_confirmation-{{ $user->id }}">Confirmer le mot de passe</label>
                                                        <input class="form-control" id="password_confirmation-{{ $user->id }}" type="password" name="password_confirmation" required minlength="8">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mt-3 px-3 pb-3">
                <p class="text-muted small mb-0">
                    Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} utilisateurs
                </p>
                {{ $users->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
