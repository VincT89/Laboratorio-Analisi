@extends('layouts.app')
@section('title', 'Modifica Stato di Conservazione')

@section('breadcrumb')
    <a href="{{ route('conservation-statuses.index') }}" class="breadcrumb-item">Stati di Conservazione</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Modifica</span>
@endsection

@section('content')
<div class="form-card-wrap" style="max-width: 600px; margin: 0 auto;">
    <div class="form-card-header">
        <h2 class="form-card-title">Modifica Stato di Conservazione</h2>
    </div>
    <div class="form-card-body">
        <form action="{{ route('conservation-statuses.update', $conservationStatus) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name" class="form-label required">Nome Stato</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $conservationStatus->name) }}" required autofocus>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="custom-checkbox">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $conservationStatus->is_active) ? 'checked' : '' }}>
                    <span>Stato attivo (selezionabile nei nuovi campioni)</span>
                </label>
            </div>

            <div class="form-actions" style="margin-top: 2rem;">
                <a href="{{ route('conservation-statuses.index') }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
            </div>
        </form>
    </div>
</div>
@endsection
