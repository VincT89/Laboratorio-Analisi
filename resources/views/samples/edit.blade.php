@extends('layouts.app')
@section('title', 'Modifica Campione '.$sample->code)

@section('breadcrumb')
    <a href="{{ route('samples.index') }}" class="breadcrumb-item">Campioni</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('samples.show', $sample) }}" class="breadcrumb-item">{{ $sample->code }}</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Modifica</span>
@endsection

@section('content')
<div class="form-card-wrap">
    <div class="form-card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <h2 class="form-card-title">Modifica Campione {{ $sample->code }}</h2>
        <span class="badge
            @if($sample->status === 'completed') badge-completed
            @elseif($sample->status === 'accepted') badge-accepted
            @else badge-collected
            @endif">
            <span class="badge-dot"></span>
            {{ $sample->status === 'collected' ? 'Prelevato' : ($sample->status === 'accepted' ? 'Accettato' : 'Completato') }}
        </span>
    </div>

    <div class="form-card-body">
        <form action="{{ route('samples.update', $sample) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="client_id" class="form-label required">Cliente</label>
                <select name="client_id" id="client_id" class="form-control" required>
                    <option value="">-- Seleziona un cliente --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ (old('client_id', $sample->client_id) == $client->id) ? 'selected' : '' }}>
                            {{ $client->company_name ?: $client->first_name . ' ' . $client->last_name }}
                            {{ $client->tax_code ? '('.$client->tax_code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('client_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="collected_at" class="form-label required">Data Prelievo</label>
                    <input type="date" name="collected_at" id="collected_at" class="form-control"
                        value="{{ old('collected_at', $sample->collected_at ? \Carbon\Carbon::parse($sample->collected_at)->format('Y-m-d') : '') }}" required>
                    @error('collected_at') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="sample_type_id" class="form-label required">Tipo Campione</label>
                    <select name="sample_type_id" id="sample_type_id" class="form-control" required>
                        <option value="">-- Seleziona il tipo --</option>
                        @foreach($sampleTypes as $type)
                            <option value="{{ $type->id }}" {{ old('sample_type_id', $sample->sample_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} {{ !$type->is_active ? '(Disattivato)' : '' }}
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
                        value="{{ old('collection_site', $sample->collection_site) }}" required>
                    @error('collection_site') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="collected_by" class="form-label required">Prelevatore</label>
                    <input type="text" name="collected_by" id="collected_by" class="form-control"
                        value="{{ old('collected_by', $sample->collected_by) }}" required>
                    @error('collected_by') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Lo stato del campione è gestito tramite azioni dedicate (accept/complete) e non da questo form --}}
            <div class="form-section-box">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Data Accettazione Laboratorio</label>
                        <p class="form-control-static">
                            {{ $sample->accepted_at ? \Carbon\Carbon::parse($sample->accepted_at)->format('d/m/Y') : 'In attesa' }}
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stato del Campione</label>
                        <p class="form-control-static" style="font-weight: 500;">
                            {{ $sample->status === 'collected' ? 'Prelevato' : ($sample->status === 'accepted' ? 'Accettato' : 'Completato') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Note (Opzionale)</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $sample->notes) }}</textarea>
                @error('notes') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('samples.show', $sample) }}" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
            </div>
        </form>
    </div>
</div>
@endsection