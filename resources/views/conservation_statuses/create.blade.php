@extends('layouts.app')
@section('title', 'Nuovo Stato di Conservazione')

@section('breadcrumb')
    <a href="{{ route('conservation-statuses.index') }}" class="breadcrumb-item">Stati di Conservazione</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo</span>
@endsection

@section('content')
<div class="form-card-wrap" style="max-width: 600px; margin: 0 auto;">
    <div class="form-card-header">
        <h2 class="form-card-title">Nuovo Stato di Conservazione</h2>
    </div>
    <div class="form-card-body">
        <form action="{{ route('conservation-statuses.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label required">Nome Stato (es: Ambiente, Refrigerato)</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('conservation-statuses.index') }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Stato</button>
            </div>
        </form>
    </div>
</div>
@endsection
