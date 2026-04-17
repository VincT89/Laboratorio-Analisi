<section>
    <div class="profile-section-title">Modifica Password</div>
    <div class="profile-section-sub">Assicurati che il tuo account utilizzi una password lunga e complessa per rimanere protetto.</div>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password" class="form-label">Password Attuale</label>
            <input id="update_password_current_password" name="current_password" type="password"
                class="form-control" style="max-width:480px"
                autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="update_password_password" class="form-label">Nuova Password</label>
            <input id="update_password_password" name="password" type="password"
                class="form-control" style="max-width:480px"
                autocomplete="new-password">
            @error('password', 'updatePassword')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation" class="form-label">Conferma Nuova Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="form-control" style="max-width:480px"
                autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="profile-form-footer">
            <button type="submit" class="btn btn-primary">Aggiorna Password</button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="profile-saved-msg">
                   Password aggiornata con successo.
                </p>
            @endif
        </div>
    </form>
</section>