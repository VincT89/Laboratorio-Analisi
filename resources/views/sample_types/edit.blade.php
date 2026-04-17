@extends('layouts.app')
@section('title', 'Modifica Tipo di Campione')

@section('breadcrumb')
    <a href="{{ route('sample-types.index') }}" class="breadcrumb-item">Tipi di Campione</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">{{ $sampleType->name }}</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Modifica Tipo: {{ $sampleType->name }}</h2>
        </div>
        
        <div class="form-card-body">
            <p class="form-subtitle">Aggiorna il nome o lo stato di questo tipo di campione.</p>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sample-types.update', $sampleType) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome del Tipo</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $sampleType->name) }}" required>
                        <small class="form-help-text">Se modificato, si rifletterà su tutti i campioni collegati storicamente (non altera il link relazionale).</small>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Stato e Privacy</div>
                    
                    <div class="form-group mb-4">
                        <label class="client-type-option">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sampleType->is_active) ? 'checked' : '' }}>
                            <span>Attivo (Selezionabile in inserimento)</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="client-type-option">
                            <input type="checkbox" name="is_sensitive" value="1" {{ old('is_sensitive', $sampleType->is_sensitive ?? false) ? 'checked' : '' }}>
                            <span>Tipo Sensibile (Privacy Elevata)</span>
                        </label>
                        <small class="form-help-text">Se attivo, lo staff vedrà solo codice campione e stato workflow.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sample-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Aggiorna Tipo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
