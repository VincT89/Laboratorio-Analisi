@extends('layouts.app')
@section('title', 'Nuovo Tipo di Contenitore')

@section('breadcrumb')
    <a href="{{ route('container-types.index') }}" class="breadcrumb-item">Tipi di Contenitore</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Nuovo Tipo di Contenitore</h2>
        </div>
        
        <div class="form-card-body">
            <form action="{{ route('container-types.store') }}" method="POST">
                @csrf

                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome Contenitore</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" autofocus>
                        @error('name')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('container-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Contenitore</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
