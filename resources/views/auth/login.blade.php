<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Consulting — Accedi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body>

<div class="auth-shell">

    {{-- Brand panel sinistro --}}
    <div class="auth-brand">
        <div class="auth-brand-logo">
            <img src="{{ asset('images/logo.png') }}" alt="AER Consulting Logo" height="80" width="80">
            <span class="auth-brand-logo-text">AER Consulting</span>
        </div>

        <div>
            <div class="auth-brand-tagline">
                Bentornato nel<br>tuo laboratorio
            </div>
            <div class="auth-brand-sub">
                Inserisci le tue credenziali per accedere al gestionale.
            </div>
        </div>
    </div>

    {{-- Form login --}}
    <div class="auth-content">
        <div class="auth-card">

            <div class="auth-card-title">Accedi</div>
            <div class="auth-card-sub">Inserisci email e password</div>

            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label required" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="nome@laboratorio.it"
                        required autofocus>
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label required" for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="••••••••"
                        required>
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-form-row">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember">
                        <span>Ricordami</span>
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-forgot">Password dimenticata?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary auth-submit-btn">Accedi</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>