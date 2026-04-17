<section>
    <div class="profile-section-title">Informazioni Profilo</div>
    <div class="profile-section-sub">Aggiorna il nome e l'indirizzo email associato al tuo account di accesso.</div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name" class="form-label required">Nome e Cognome</label>
            <input id="name" name="name" type="text" class="form-control"
                style="max-width:480px"
                value="{{ old('name', $user->name) }}"
                required autofocus autocomplete="name">
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label required">Indirizzo Email</label>
            <input id="email" name="email" type="email" class="form-control"
                style="max-width:480px"
                value="{{ old('email', $user->email) }}"
                required autocomplete="username">
            @error('email') <span class="form-error">{{ $message }}</span> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="profile-verify-notice">
                    <p class="profile-verify-text">
                        Il tuo indirizzo email non è stato verificato.
                        <button form="send-verification" class="profile-verify-btn">
                            Clicca qui per inviare nuovamente il link di verifica.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="profile-verify-sent">Un nuovo link di verifica è stato inviato al tuo indirizzo email.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="profile-form-footer">
            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="profile-saved-msg">
                   Aggiornamento completato.
                </p>
            @endif
        </div>
    </form>
</section>