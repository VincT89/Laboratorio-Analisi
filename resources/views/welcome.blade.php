<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Consulting — Gestionale Laboratorio</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
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
            <img src="{{ asset('images/logo.png') }}" alt="AER Consulting Logo" height="200" width="200">
        </div>

        <div>
            <div class="auth-brand-tagline">
                Ogni campione.<br>Ogni cliente. Sotto controllo.
            </div>
            <div class="auth-brand-sub">
                Il gestionale pensato per chi lavora in laboratorio:
                preciso, veloce e sempre a portata di mano.
            </div>

            <div class="auth-brand-features">
                <div class="auth-brand-feature">
                    <span class="auth-brand-feature-dot"></span>
                    Stato del campione aggiornato in tempo reale
                </div>
                <div class="auth-brand-feature">
                    <span class="auth-brand-feature-dot"></span>
                    Documenti e referti sempre al sicuro
                </div>
                <div class="auth-brand-feature">
                    <span class="auth-brand-feature-dot"></span>
                    Cronologia completa di ogni operazione
                </div>
                <div class="auth-brand-feature">
                    <span class="auth-brand-feature-dot"></span>
                    Accessi differenziati per admin e operatori
                </div>
            </div>
        </div>
    </div>

    {{-- Contenuto destro --}}
    <div class="auth-content">
        <div class="auth-welcome">
            <h1 class="auth-welcome-title">Benvenuto</h1>
            <p class="auth-welcome-sub">Inserisci le tue credenziali per entrare<br>nella tua area di lavoro.</p>
            <div class="auth-welcome-actions">
                <a href="{{ route('login') }}" class="btn btn-primary auth-welcome-btn">Accedi al gestionale</a>
            </div>
        </div>
    </div>

</div>

</body>
</html>