@extends('layouts.app')
@section('title', 'Modifica Tipo di Documento')

@section('breadcrumb')
    <a href="{{ route('document-types.index') }}" class="breadcrumb-item">Tipi di Documento</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Modifica</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Modifica Tipo: {{ $documentType->name }}</h2>
        </div>

        <div class="form-card-body">
            <form action="{{ route('document-types.update', $documentType) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome Tipo Documento</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $documentType->name) }}" required maxlength="255">
                        @error('name')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="sort_order">Ordine nel Menu</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $documentType->sort_order) }}" required min="0" max="9999">
                        @error('sort_order')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Stato e Visibilità</div>
                    <div class="form-group">
                        <label class="client-type-option">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }}>
                            <span>Tipo attivo e selezionabile nei nuovi caricamenti</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('document-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Aggiorna Tipo Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
