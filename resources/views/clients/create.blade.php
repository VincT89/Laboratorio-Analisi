@extends('layouts.app')
@section('title', 'Nuovo Cliente')

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="breadcrumb-item">Clienti</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo</span>
@endsection

@section('content')
<div class="form-card-wrap" x-data="{ type: '{{ old('type', 'company') }}' }">
    <div class="form-card-header">
        <h2 class="form-card-title">Inserisci Nuovo Cliente</h2>
    </div>

    <div class="form-card-body">
        <form action="{{ route('clients.store') }}" method="POST">
            @csrf

            {{-- Tipologia --}}
            <div class="form-section">
                <div class="form-section-title">Tipologia Cliente</div>
                <div class="client-type-toggle">
                    <label class="client-type-option">
                        <input type="radio" name="type" value="company" x-model="type">
                        <span>Azienda / P.IVA</span>
                    </label>
                    <label class="client-type-option">
                        <input type="radio" name="type" value="individual" x-model="type">
                        <span>Privato</span>
                    </label>
                </div>
                @error('type') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- Anagrafica --}}
            <div class="form-section">
                <div class="form-section-title">Dati Anagrafici</div>
                <div class="form-group">
                    <label for="company_name" class="form-label required">Ragione Sociale / Nominativo Principale</label>
                    <input type="text" name="company_name" id="company_name" class="form-control"
                        value="{{ old('company_name') }}" required>
                    @error('company_name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-grid-2" x-show="type === 'individual'" x-cloak>
                    <div class="form-group">
                        <label for="first_name" class="form-label">Nome Referente/Privato</label>
                        <input type="text" name="first_name" id="first_name" class="form-control"
                            value="{{ old('first_name') }}">
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Cognome Referente/Privato</label>
                        <input type="text" name="last_name" id="last_name" class="form-control"
                            value="{{ old('last_name') }}">
                    </div>
                </div>
            </div>

            {{-- Contatti --}}
            <div class="form-section">
                <div class="form-section-title">Contatti</div>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email') }}">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Telefono</label>
                        <input type="text" name="phone" id="phone" class="form-control"
                            value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label for="pec" class="form-label">PEC</label>
                        <input type="email" name="pec" id="pec" class="form-control"
                            value="{{ old('pec') }}">
                    </div>
                </div>
            </div>

            {{-- Sede e Fatturazione --}}
            <div class="form-grid-2">
                <div class="form-section">
                    <div class="form-section-title">Sede</div>
                    <div class="form-group">
                        <label for="address" class="form-label">Indirizzo</label>
                        <input type="text" name="address" id="address" class="form-control"
                            value="{{ old('address') }}">
                    </div>
                    <div class="form-grid-3" style="grid-template-columns: 2fr 1fr;">
                        <div class="form-group">
                            <label for="city" class="form-label">Città</label>
                            <input type="text" name="city" id="city" class="form-control"
                                value="{{ old('city') }}">
                        </div>
                        <div class="form-group">
                            <label for="province" class="form-label">Prov.</label>
                            <input type="text" name="province" id="province" class="form-control"
                                value="{{ old('province') }}" maxlength="5">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="postal_code" class="form-label">CAP</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control"
                                value="{{ old('postal_code') }}" maxlength="10">
                        </div>
                        <div class="form-group">
                            <label for="country" class="form-label">Nazione</label>
                            <input type="text" name="country" id="country" class="form-control"
                                value="{{ old('country', 'Italia') }}">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Fatturazione</div>
                    <div class="form-group">
                        <label for="tax_code" class="form-label">Codice Fiscale</label>
                        <input type="text" name="tax_code" id="tax_code" class="form-control"
                            value="{{ old('tax_code') }}" style="text-transform:uppercase">
                    </div>
                    <div class="form-group" x-show="type === 'company'">
                        <label for="vat_number" class="form-label">Partita IVA</label>
                        <input type="text" name="vat_number" id="vat_number" class="form-control"
                            value="{{ old('vat_number') }}">
                    </div>
                    <div class="form-group" x-show="type === 'company'">
                        <label for="sdi_code" class="form-label">Codice SDI</label>
                        <input type="text" name="sdi_code" id="sdi_code" class="form-control"
                            value="{{ old('sdi_code') }}" maxlength="7" style="text-transform:uppercase">
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <div class="form-section">
                <div class="form-group">
                    <label for="notes" class="form-label">Note / Altre Informazioni</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection