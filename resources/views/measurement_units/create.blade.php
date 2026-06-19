@extends('layouts.app')
@section('title', 'Nuova Unità di Misura')

@section('breadcrumb')
    <a href="{{ route('measurement-units.index') }}" class="breadcrumb-item">Unità di Misura</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuova</span>
@endsection

@section('content')
<div class="form-card-wrap" style="max-width: 600px; margin: 0 auto;">
    <div class="form-card-header">
        <h2 class="form-card-title">Nuova Unità di Misura</h2>
    </div>
    <div class="form-card-body">
        <form action="{{ route('measurement-units.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label required">Nome Unità (es: kg, ml, pz)</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('measurement-units.index') }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Unità</button>
            </div>
        </form>
    </div>
</div>
@endsection
