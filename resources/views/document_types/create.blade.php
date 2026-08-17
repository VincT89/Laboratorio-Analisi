@extends('layouts.app')
@section('title', 'Nuovo Tipo di Documento')

@section('breadcrumb')
    <a href="{{ route('document-types.index') }}" class="breadcrumb-item">Tipi di Documento</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Nuovo</span>
@endsection

@section('content')
<div class="page-wrap">
    <div class="form-card-wrap">
        <div class="form-card-header">
            <h2 class="form-card-title">Nuovo Tipo di Documento</h2>
        </div>

        <div class="form-card-body">
            <form action="{{ route('document-types.store') }}" method="POST">
                @csrf

                <div class="form-section">
                    <div class="form-section-title">Dati Principali</div>
                    <div class="form-group">
                        <label class="form-label required" for="name">Nome Tipo Documento</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" autofocus>
                        @error('name')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sort_order">Ordine nel Menu</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order') }}" min="0" max="9999">
                        <small class="form-help-text">Se non indicato, il tipo verrà aggiunto in fondo al menu.</small>
                        @error('sort_order')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('document-types.index') }}" class="btn btn-secondary">Annulla</a>
                    <button type="submit" class="btn btn-primary">Salva Tipo Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
