@extends('layouts.app')
@section('title', 'Modifica Tipo di Contenitore')

@section('breadcrumb')
    <a href="{{ route('container-types.index') }}" class="breadcrumb-item">Tipi di Contenitore</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Modifica</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Modifica Contenitore: {{ $containerType->name }}</h2>
        </div>
        
        <div class="form-card-body">
            <form action="{{ route('container-types.update', $containerType) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome Contenitore</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $containerType->name) }}" required maxlength="255">
                        @error('name')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Stato e Visibilità</div>
                    <div class="form-group">
                        <label class="client-type-option">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $containerType->is_active) ? 'checked' : '' }}>
                            <span>Tipo di contenitore attivo (selezionabile per nuovi campioni)</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('container-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Aggiorna Contenitore</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
