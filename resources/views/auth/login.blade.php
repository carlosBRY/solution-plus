<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-success" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
        @csrf

        <div class="mb-4">
            <p class="eyebrow mb-1">Accès Sécurisé</p>
            <h1 class="h3 mb-1">Connexion</h1>
            <p class="text-muted mb-0">Connectez-vous à votre espace de gestion.</p>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label class="form-label" for="email">Adresse e-mail</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Mot de passe</label>
                @if (Route::has('password.request'))
                    <a class="small fw-semibold text-primary" href="{{ route('password.request') }}">Oublié ?</a>
                @endif
            </div>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

     

        <button class="btn btn-primary w-100 py-2 fs-6 fw-semibold" type="submit">
            <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i> Se connecter
        </button>
    </form>

   
</x-guest-layout>
