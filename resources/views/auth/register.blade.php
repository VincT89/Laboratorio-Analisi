<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Consulting — Registrati</title>
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
                Crea il tuo<br>account
            </div>
            <div class="auth-brand-sub">
                Registrati per accedere al gestionale del laboratorio.
            </div>
        </div>
    </div>

    {{-- Form register --}}
    <div class="auth-content">
        <div class="auth-card">

            <div class="auth-card-title">Registrati</div>
            <div class="auth-card-sub">Compila i campi per creare l'account</div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label required" for="name">Nome</label>
                    <input type="text" id="name" name="name"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name') }}"
                        placeholder="Mario Rossi"
                        required autofocus>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label required" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="nome@laboratorio.it"
                        required>
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

                <div class="form-group">
                    <label class="form-label required" for="password_confirmation">Conferma password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control"
                        placeholder="••••••••"
                        required>
                </div>

                <button type="submit" class="btn btn-primary auth-submit-btn" style="margin-top:4px;">
                    Crea account
                </button>

            </form>

            <div class="auth-card-footer">
                Hai già un account? <a href="{{ route('login') }}">Accedi</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>