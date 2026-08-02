<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="eyebrow mb-1 text-muted">Administration</p>
                <h1 class="h3 mb-0">Gestion des Rôles & Permissions</h1>
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
            <a class="nav-link" href="{{ route('parametres.comptes.index') }}">
                <i class="bi bi-wallet2 me-1"></i> Comptes & Moyens de Paiement
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('parametres.conditionnements') }}">
                <i class="bi bi-box-seam me-1"></i> Conditionnements
            </a>
        </li>
        @can('gérer-utilisateurs')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('parametres.users.index') }}">
                    <i class="bi bi-people me-1"></i> Utilisateurs
                </a>
            </li>
        @endcan
        @can('gérer-roles')
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('parametres.roles.index') }}">
                    <i class="bi bi-shield-lock me-1"></i> Rôles & Permissions
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Liste des Rôles --}}
        <div class="col-12 col-xl-5">
            <section class="panel">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-0 section-title"><i class="bi bi-people-fill me-2"></i>Rôles Existants</h2>
                        <small class="text-muted">{{ $roles->count() }} rôle(s) configuré(s)</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="bi bi-plus-circle me-1"></i> Nouveau Rôle
                    </button>
                </div>

                <div class="list-group list-group-flush">
                    @foreach($roles as $role)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        @if($role->name === 'Administrateur')
                                            <i class="bi bi-shield-fill-check text-danger me-1"></i>
                                        @else
                                            <i class="bi bi-person-badge me-1 text-primary"></i>
                                        @endif
                                        {{ $role->name }}
                                    </h6>
                                    <small class="text-muted">
                                        {{ $role->users->count() }} utilisateur(s) &bull;
                                        {{ $role->permissions->count() }} permission(s)
                                    </small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoleModal-{{ $role->id }}" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($role->name !== 'Administrateur')
                                        <form method="POST" action="{{ route('parametres.roles.destroy', $role) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer le rôle \'{{ $role->name }}\' ?')" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($role->permissions as $perm)
                                    @php
                                        $isSensitive = in_array($perm->name, ['ajuster-stock', 'modifier-solde-compte', 'gérer-roles', 'valider-détérioration', 'annuler-vente']);
                                    @endphp
                                    <span class="badge {{ $isSensitive ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }} small">
                                        {{ $perm->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Matrice des Permissions --}}
        <div class="col-12 col-xl-7">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="h5 mb-0 section-title"><i class="bi bi-key me-2"></i>Matrice des Permissions</h2>
                    <small class="text-muted">Les permissions marquées 🔒 sont des actions sensibles et doivent être attribuées avec prudence.</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                @foreach($roles as $role)
                                    <th class="text-center small" style="min-width: 80px;">{{ $role->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionGroups as $groupName => $groupPermissions)
                                <tr class="table-secondary">
                                    <td colspan="{{ $roles->count() + 1 }}" class="fw-bold small text-uppercase py-1">
                                        {{ $groupName }}
                                    </td>
                                </tr>
                                @foreach($groupPermissions as $permName => $permLabel)
                                    @php
                                        $isSensitive = str_starts_with($permLabel, '🔒');
                                    @endphp
                                    <tr>
                                        <td class="small {{ $isSensitive ? 'text-danger fw-semibold' : '' }}">
                                            {{ $permLabel }}
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ $permName }}</div>
                                        </td>
                                        @foreach($roles as $role)
                                            <td class="text-center">
                                                @if($role->hasPermissionTo($permName))
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-x-circle text-muted opacity-25"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Modal: Créer un rôle --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('parametres.roles.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createRoleModalLabel"><i class="bi bi-shield-plus me-2"></i>Créer un Nouveau Rôle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="create_role_name">Nom du Rôle <span class="text-danger">*</span></label>
                            <input class="form-control" id="create_role_name" type="text" name="name" placeholder="Ex: Superviseur, Livreur..." required>
                        </div>

                        <h6 class="fw-bold mb-2"><i class="bi bi-key me-1"></i>Permissions à attribuer</h6>

                        @foreach($permissionGroups as $groupName => $groupPermissions)
                            <div class="mb-3">
                                <div class="fw-semibold small text-uppercase text-muted border-bottom pb-1 mb-2">{{ $groupName }}</div>
                                <div class="row g-1">
                                    @foreach($groupPermissions as $permName => $permLabel)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permName }}" id="create_perm_{{ Str::slug($permName) }}">
                                                <label class="form-check-label small {{ str_starts_with($permLabel, '🔒') ? 'text-danger fw-semibold' : '' }}" for="create_perm_{{ Str::slug($permName) }}">
                                                    {{ $permLabel }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Créer le Rôle</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modals: Modifier chaque rôle --}}
    @foreach($roles as $role)
        <div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1" aria-labelledby="editRoleModalLabel-{{ $role->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form method="POST" action="{{ route('parametres.roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editRoleModalLabel-{{ $role->id }}"><i class="bi bi-pencil-square me-2"></i>Modifier le Rôle : {{ $role->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom du Rôle <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="name" value="{{ $role->name }}" required {{ $role->name === 'Administrateur' ? 'readonly' : '' }}>
                            </div>

                            <h6 class="fw-bold mb-2"><i class="bi bi-key me-1"></i>Permissions</h6>

                            @foreach($permissionGroups as $groupName => $groupPermissions)
                                <div class="mb-3">
                                    <div class="fw-semibold small text-uppercase text-muted border-bottom pb-1 mb-2">{{ $groupName }}</div>
                                    <div class="row g-1">
                                        @foreach($groupPermissions as $permName => $permLabel)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                        id="edit_{{ $role->id }}_perm_{{ Str::slug($permName) }}"
                                                        {{ $role->hasPermissionTo($permName) ? 'checked' : '' }}>
                                                    <label class="form-check-label small {{ str_starts_with($permLabel, '🔒') ? 'text-danger fw-semibold' : '' }}" for="edit_{{ $role->id }}_perm_{{ Str::slug($permName) }}">
                                                        {{ $permLabel }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
