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
                <div
                    x-data="clientSearch({
                        searchUrl: @js(route('clients.search')),
                        initialId: @js(old('client_id', $sample->client_id)),
                        initialText: @js(old('client_text', $sample->client ? (($sample->client->company_name ?: trim($sample->client->first_name . ' ' . $sample->client->last_name)) . ($sample->client->tax_code ? ' ('.$sample->client->tax_code.')' : '')) : ''))
                    })"
                    class="client-search"
                    @click.away="open = false"
                >
                    <input type="hidden" name="client_id" id="client_id" x-model="selectedId" required>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Cerca cliente..."
                        x-model="query"
                        @input.debounce.250ms="searchClients"
                        @focus="open = true"
                        autocomplete="off"
                    >

                    <div x-show="open && results.length > 0" x-cloak class="client-search-results">
                        <template x-for="client in results" :key="client.id">
                            <button
                                type="button"
                                class="client-search-result"
                                @click="selectClient(client)"
                            >
                                <span x-text="client.text"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="open && query.length > 0 && !loading && results.length === 0" x-cloak class="client-search-empty">
                        Nessun cliente trovato.
                    </div>

                    <div x-show="loading" x-cloak class="client-search-empty">
                        Ricerca...
                    </div>
                </div>
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

            @if(auth()->user()->isAdmin())
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="code_progressive" class="form-label">Progressivo Campione (Solo Admin)</label>
                <input type="number" name="code_progressive" id="code_progressive" class="form-control"
                    value="{{ old('code_progressive', $sample->code_progressive) }}" min="1" max="9999" style="max-width: 200px;">
                <small style="color: #7A88A0; font-size: 13px; display: block; margin-top: 4px;">Attenzione: modificare il progressivo cambierà il codice del campione. Questo può impattare la tracciabilità.</small>
                @error('code_progressive') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="collection_site" class="form-label">Punto di Prelievo</label>
                    <input type="text" name="collection_site" id="collection_site" class="form-control"
                        value="{{ old('collection_site', $sample->collection_site) }}">
                    @error('collection_site') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="collected_by" class="form-label required">Prelevatore</label>
                    <input type="text" name="collected_by" id="collected_by" class="form-control"
                        value="{{ old('collected_by', $sample->collected_by) }}" required>
                    @error('collected_by') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="lab_archived_by_name" class="form-label">Nominativo di chi archivia in lab.</label>
                    <input type="text" name="lab_archived_by_name" id="lab_archived_by_name" class="form-control"
                        value="{{ old('lab_archived_by_name', $sample->lab_archived_by_name) }}">
                    @error('lab_archived_by_name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="container_type_id" class="form-label">Tipologia Contenitore</label>
                    <select name="container_type_id" id="container_type_id" class="form-control">
                        <option value="">-- Seleziona --</option>
                        @foreach($containerTypes as $containerType)
                            <option value="{{ $containerType->id }}" {{ old('container_type_id', $sample->container_type_id) == $containerType->id ? 'selected' : '' }}>
                                {{ $containerType->name }} {{ !$containerType->is_active ? '(Disattivato)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('container_type_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="conservation_status" class="form-label">Stato di conservazione campione</label>
                    <input type="text" name="conservation_status" id="conservation_status" class="form-control"
                        value="{{ old('conservation_status', $sample->conservation_status) }}">
                    @error('conservation_status') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="sample_quantity" class="form-label">Quantità campione</label>
                    <input type="text" name="sample_quantity" id="sample_quantity" class="form-control"
                        value="{{ old('sample_quantity', $sample->sample_quantity) }}">
                    @error('sample_quantity') <span class="form-error">{{ $message }}</span> @enderror
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

@push('styles')
<style>
.client-search {
    position: relative;
}
.client-search-results {
    position: absolute;
    z-index: 30;
    width: 100%;
    background: #fff;
    border: 1px solid #D4DBE8;
    border-radius: 8px;
    margin-top: 4px;
    box-shadow: 0 8px 24px rgba(10,16,32,0.08);
    overflow: hidden;
    max-height: 300px;
    overflow-y: auto;
}
.client-search-result {
    display: block;
    width: 100%;
    text-align: left;
    padding: 10px 12px;
    background: #fff;
    border: 0;
    cursor: pointer;
    font-size: 14px;
    color: #18243A;
    border-bottom: 1px solid #F4F6FB;
}
.client-search-result:last-child {
    border-bottom: 0;
}
.client-search-result:hover {
    background: #F4F6FB;
}
.client-search-empty {
    margin-top: 6px;
    font-size: 0.85rem;
    color: #7A88A0;
}
</style>
@endpush

@push('scripts')
<script>
    function clientSearch({ searchUrl, initialId = '', initialText = '' }) {
        return {
            query: initialText,
            selectedId: initialId,
            results: [],
            open: false,
            loading: false,

            async searchClients() {
                this.selectedId = '';

                const q = this.query.trim();

                if (q.length < 1) {
                    this.results = [];
                    this.open = false;
                    return;
                }

                this.loading = true;
                this.open = true;

                try {
                    const response = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Errore ricerca clienti');
                    }

                    this.results = await response.json();
                } catch (error) {
                    console.error(error);
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            selectClient(client) {
                this.selectedId = client.id;
                this.query = client.text;
                this.results = [];
                this.open = false;
            }
        };
    }
</script>
@endpush
@endsection