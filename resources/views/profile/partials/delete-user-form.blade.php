<section>
    <div class="profile-section-title">Elimina Account</div>
    <div class="profile-section-sub">
        Una volta eliminato, tutte le risorse e i dati associati all'account verranno rimossi definitivamente.
        Scarica eventuali dati che desideri conservare prima di procedere.
    </div>

    <button
        class="btn btn-danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Elimina Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="profile-delete-modal">
            @csrf
            @method('delete')

            <h2 class="profile-delete-modal-title">Sei sicuro di voler eliminare il tuo account?</h2>
            <p class="profile-delete-modal-sub">
                Questa operazione è irreversibile. Tutti i tuoi dati verranno eliminati definitivamente.
                Inserisci la tua password per confermare.
            </p>

            <div class="form-group" style="margin-top:20px;">
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password"
                    class="form-control" style="max-width:360px"
                    placeholder="••••••••">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="form-error" />
            </div>

            <div class="profile-delete-modal-footer">
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close')">Annulla</button>
                <button type="submit" class="btn btn-danger">Elimina Account</button>
            </div>
        </form>
    </x-modal>
</section>