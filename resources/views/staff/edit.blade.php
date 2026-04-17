@extends('layouts.app')
@section('title', 'Modifica Membro Staff')

@section('breadcrumb')
    <a href="{{ route('staff.index') }}" class="breadcrumb-item">Gestione Staff</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Modifica: {{ $staff->name }}</span>
@endsection

@section('content')
<div class="form-card-wrap">
    <div class="form-card-header">
        <h2 class="form-card-title">Modifica Utente</h2>
        <p class="form-card-sub">Aggiorna le credenziali o i permessi. Lascia vuoto il campo password se non vuoi modificarla.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('staff.update', $staff) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="name" class="form-label required">Nome e Cognome</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="{{ old('name', $staff->name) }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="email" class="form-label required">Indirizzo Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="{{ old('email', $staff->email) }}" required>
                    @error('email') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Reset Sicurezza</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="password" class="form-label">Nuova Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Lascia vuoto per non cambiare">
                        @error('password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Conferma Nuova Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                            placeholder="Ripeti se compilata">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Permessi di Accesso</div>
                <div class="form-group">
                    <label for="role" class="form-label required">Ruolo Operativo</label>
                    <select name="role" id="role" class="form-control" style="max-width:420px;" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $staff->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                                {{ $role->name == 'admin' ? '(Accesso Completo Totale)' : '(Nessun permesso di eliminazione)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <span class="form-error">{{ $message }}</span> @enderror
                    <span class="form-hint">Attenzione: assegnare poteri da Admin garantisce controllo incondizionato.</span>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
            </div>
        </form>
    </div>
</div>
@endsection