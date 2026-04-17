@extends('layouts.app')
@section('title', 'Nuovo Campione')

@section('breadcrumb')
    <a href="{{ route('samples.index') }}" class="breadcrumb-item">Campioni</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo</span>
@endsection

@section('content')
@if(empty($mode) || !in_array($mode, ['standard', 'sensitive']))
    {{-- Step 1: Selezione Modalità --}}
    <div class="page-wrap" style="max-width: 800px; margin: 0 auto; padding-top: 3rem;">
        
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 56px; height: 56px; background: rgba(7,133,134,0.08); color: #078586; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <h2 style="color: #18243A; font-size: 24px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.02em;">Nuovo Campione</h2>
            <p style="color: #7A88A0; font-size: 15px;">Seleziona il flusso di registrazione più adatto al prelievo da eseguire.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <a href="{{ route('samples.create', ['mode' => 'standard', 'client_id' => request('client_id')]) }}" class="dash-card" style="background: #fff; text-decoration: none; padding: 36px 32px; display: block; text-align: left; border: 1px solid #D4DBE8; box-shadow: 0 2px 8px rgba(10,16,32,0.04); border-radius: 12px; transition: all 0.2s; position: relative;">
                <div style="width: 48px; height: 48px; background: #F4F6FB; color: #18243A; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 style="color: #18243A; margin-bottom: 12px; font-size: 17px; font-weight: 600;">Campione standard</h3>
                <p style="color: #4A5A72; font-size: 14px; line-height: 1.6; margin: 0;">Registrazione completa di un campione ordinario. Richiede l'inserimento immediato dell'anagrafica cliente e di tutti i dettagli operativi e tecnici del prelievo.</p>
            </a>
            
            <a href="{{ route('samples.create', ['mode' => 'sensitive']) }}" class="dash-card" style="background: #fff; text-decoration: none; padding: 36px 32px; display: block; text-align: left; border: 1px solid #D4DBE8; box-shadow: 0 2px 8px rgba(10,16,32,0.04); border-radius: 12px; transition: all 0.2s; position: relative;">
                <div style="width: 48px; height: 48px; background: rgba(220, 38, 38, 0.08); color: #DC2626; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 style="color: #B91C1C; margin-bottom: 12px; font-size: 17px; font-weight: 600;">Preregistrazione Tecnica (Anonima)</h3>
                <p style="color: #4A5A72; font-size: 14px; line-height: 1.6; margin: 0;">Preregistrazione tecnica minimale. I dettagli anagrafici e operativi saranno omessi per tutelare l'anonimato del cliente, e completati in seguito dall'amministratore.</p>
            </a>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="{{ route('samples.index') }}" class="btn btn-secondary" style="padding: 10px 20px; font-weight: 500;">Annulla e torna alla lista</a>
        </div>
    </div>
    
    <style>
        .dash-card:hover {
            transform: translateY(-3px);
            border-color: #078586 !important;
            box-shadow: 0 12px 30px rgba(10,16,32,0.06) !important;
        }
    </style>
@else
    {{-- Step 2: Form per la compilazione --}}
    <div class="page-wrap" x-data="{ showClientModal: false, clientType: 'company' }">
        <div class="form-card-wrap">
            <div class="form-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <h2 class="form-card-title">{{ $mode === 'sensitive' ? 'Nuova Preregistrazione Tecnica (Anonima)' : 'Inserisci Nuovo Campione Standard' }}</h2>
                <a href="{{ route('samples.create', ['client_id' => request('client_id')]) }}" style="color: #4A5A72; text-decoration: none; font-size: 0.9rem; padding: 4px 12px; border-radius: 6px; background: rgba(0,0,0,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.1)'" onmouseout="this.style.background='rgba(0,0,0,0.05)'">← Cambia tipologia</a>
            </div>
            <div class="form-card-body">
                <form action="{{ route('samples.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="creation_mode" value="{{ $mode }}">

                    @if($mode === 'sensitive')
                        <div class="dash-alert dash-alert--red" style="margin-bottom: 24px;">
                            <div class="dash-alert-icon">!</div>
                            <div>
                                <div class="dash-alert-text">Modalità Anonima Attiva</div>
                                <div class="dash-alert-sub">Tutti i riferimenti anagrafici sono disabilitati. L'amministratore assegnerà i dettagli protetti successivamente.</div>
                            </div>
                        </div>
                    @endif

                    @if($mode === 'standard')
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="client_id" class="form-label required">Cliente</label>
                            <button type="button" class="new-client-btn" @click="showClientModal = true">
                                + Nuovo cliente
                            </button>
                        </div>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">-- Seleziona un cliente --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client_id', $selectedClient->id ?? '') == $client->id) ? 'selected' : '' }}>
                                    {{ $client->company_name ?: $client->first_name . ' ' . $client->last_name }}
                                    {{ $client->tax_code ? '('.$client->tax_code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="collected_at" class="form-label required">Data Prelievo</label>
                            <input type="date" name="collected_at" id="collected_at" class="form-control"
                                value="{{ old('collected_at', date('Y-m-d')) }}" required>
                            @error('collected_at') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="sample_type_id" class="form-label required">Tipo Campione</label>
                            
                            <select name="sample_type_id" class="form-control" required>
                                <option value="">-- Seleziona il tipo --</option>
                                @foreach($sampleTypes->where('is_sensitive', $mode === 'sensitive') as $type)
                                    <option value="{{ $type->id }}" {{ old('sample_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            @error('sample_type_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="collection_site" class="form-label required">Punto di Prelievo</label>
                            <input type="text" name="collection_site" id="collection_site" class="form-control"
                                value="{{ old('collection_site') }}" placeholder="Luogo o reparto..." required>
                            @error('collection_site') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="collected_by" class="form-label required">Prelevatore</label>
                            <input type="text" name="collected_by" id="collected_by" class="form-control"
                                value="{{ old('collected_by') }}" placeholder="Nome di chi ha eseguito il prelievo" required>
                            @error('collected_by') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($mode === 'standard')
                    <div class="form-group">
                        <label for="notes" class="form-label">Note (Opzionale)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                            placeholder="Informazioni cliniche o di contesto...">{{ old('notes') }}</textarea>
                        @error('notes') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="form-actions" style="margin-top: 32px;">
                        <a href="{{ route('samples.index') }}" class="btn btn-secondary">Annulla</a>
                        <button type="submit" class="btn btn-primary">
                            {{ $mode === 'sensitive' ? 'Salva Preregistrazione Tecnica' : 'Salva Campione' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($mode === 'standard')
        {{-- Modale nuovo cliente --}}
        <div class="modal-overlay" x-show="showClientModal" x-cloak @click.self="showClientModal = false">
            <div class="modal-box" x-data="{ 
                clientType: 'company',
                isSubmitting: false,
                async submitClient(event) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok && result.success) {
                            // Aggiunge la nuova opzione al dropdown
                            const selectElement = document.getElementById('client_id');
                            const newOption = new Option(result.client.text, result.client.id, true, true);
                            selectElement.appendChild(newOption);
                            
                            // Resetta e chiude
                            form.reset();
                            this.clientType = 'company';
                            showClientModal = false;
                        } else {
                            alert('Errore: ' + (result.message || 'Verifica i campi obbligatori.'));
                        }
                    } catch (error) {
                        console.error('Network Error:', error);
                        alert('Errore di connessione. Riprova.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }">
                <div class="modal-header">
                    <h3 class="modal-title">Nuovo Cliente</h3>
                    <button type="button" class="modal-close" @click="showClientModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('clients.store') }}" method="POST" id="modal-client-form" @submit.prevent="submitClient">
                        @csrf

                        {{-- Tipologia --}}
                        <div class="form-section">
                            <div class="form-section-title">Tipologia Cliente</div>
                            <div class="client-type-toggle">
                                <label class="client-type-option">
                                    <input type="radio" name="type" value="company" x-model="clientType" checked>
                                    <span>Azienda / P.IVA</span>
                                </label>
                                <label class="client-type-option">
                                    <input type="radio" name="type" value="individual" x-model="clientType">
                                    <span>Privato</span>
                                </label>
                            </div>
                        </div>

                        {{-- Anagrafica --}}
                        <div class="form-section">
                            <div class="form-section-title">Dati Anagrafici</div>
                            <div class="form-group">
                                <label class="form-label required">Ragione Sociale / Nominativo</label>
                                <input type="text" name="company_name" class="form-control" required>
                            </div>
                            <div class="form-grid-2" x-show="clientType === 'individual'" x-cloak>
                                <div class="form-group">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="first_name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cognome</label>
                                    <input type="text" name="last_name" class="form-control">
                                </div>
                            </div>
                        </div>

                        {{-- Contatti --}}
                        <div class="form-section">
                            <div class="form-section-title">Contatti</div>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">PEC</label>
                                    <input type="email" name="pec" class="form-control">
                                </div>
                            </div>
                        </div>

                        {{-- Sede e Fatturazione --}}
                        <div class="form-grid-2">
                            <div class="form-section">
                                <div class="form-section-title">Sede</div>
                                <div class="form-group">
                                    <label class="form-label">Indirizzo</label>
                                    <input type="text" name="address" class="form-control">
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Città</label>
                                        <input type="text" name="city" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Prov.</label>
                                        <input type="text" name="province" class="form-control" maxlength="5">
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label">CAP</label>
                                        <input type="text" name="postal_code" class="form-control" maxlength="10">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Nazione</label>
                                        <input type="text" name="country" class="form-control" value="Italia">
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <div class="form-section-title">Fatturazione</div>
                                <div class="form-group">
                                    <label class="form-label">Codice Fiscale</label>
                                    <input type="text" name="tax_code" class="form-control" style="text-transform:uppercase">
                                </div>
                                <div class="form-group" x-show="clientType === 'company'">
                                    <label class="form-label">Partita IVA</label>
                                    <input type="text" name="vat_number" class="form-control">
                                </div>
                                <div class="form-group" x-show="clientType === 'company'">
                                    <label class="form-label">Codice SDI</label>
                                    <input type="text" name="sdi_code" class="form-control" maxlength="7" style="text-transform:uppercase">
                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showClientModal = false">Annulla</button>
                    <button type="submit" form="modal-client-form" class="btn btn-primary">Salva Cliente</button>
                </div>
            </div>
        </div>
        @endif
    </div>
@endif
@endsection