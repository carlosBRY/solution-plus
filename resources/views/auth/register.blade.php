<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
        @csrf

        <div class="mb-4">
            <p class="eyebrow mb-1">Inscription</p>
            <h1 class="h3 mb-1">Créer un compte</h1>
            <p class="text-muted mb-0">Créez votre accès collaborateur à la cave.</p>
        </div>

        <!-- Name -->
        <div class="mb-3">
            <label class="form-label" for="name">Nom complet</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label class="form-label" for="email">Adresse e-mail</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button class="btn btn-primary w-100 py-2 fs-6 fw-semibold" type="submit">
            <i class="bi bi-person-plus me-2" aria-hidden="true"></i> S'inscrire
        </button>
    </form>

    <div class="auth-footer text-center mt-4">
        Déjà un compte ? <a href="{{ route('login') }}" class="fw-semibold">Se connecter</a>
    </div>
</x-guest-layout>
