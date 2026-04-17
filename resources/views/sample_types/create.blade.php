@extends('layouts.app')
@section('title', 'Nuovo Tipo di Campione')

@section('breadcrumb')
    <a href="{{ route('sample-types.index') }}" class="breadcrumb-item">Tipi di Campione</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo Tipo</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Nuovo Tipo di Campione</h2>
        </div>
        
        <div class="form-card-body">
            <p class="form-subtitle">Inserisci un identificativo descrittivo per questo tipo.</p>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sample-types.store') }}" method="POST">
                @csrf
                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome del Tipo</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="Es. Acqua, Emissioni, Rifiuto...">
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Privacy e Accessi</div>
                    <div class="form-group">
                        <label class="client-type-option">
                            <input type="checkbox" name="is_sensitive" value="1" {{ old('is_sensitive') ? 'checked' : '' }}>
                            <span>Tipo Sensibile (Privacy Elevata)</span>
                        </label>
                        <small class="form-help-text">Se attivo, lo staff vedrà solo codice campione e stato workflow.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sample-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Tipo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
