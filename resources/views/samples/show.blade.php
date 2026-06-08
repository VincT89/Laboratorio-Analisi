@extends('layouts.app')
@section('title', 'Dettaglio Campione '.$sample->code)

@section('breadcrumb')
    <a href="{{ route('samples.index') }}" class="breadcrumb-item">Campioni</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">{{ $sample->code }}</span>
@endsection

@section('content')
<div x-data="{ tab: 'dati' }" class="sample-detail">
    <div class="sample-box">

        {{-- Intestazione --}}
        <div class="sample-header">
        <div class="sample-header-left">
            <h1 class="sample-header-code">{{ $sample->code }}</h1>
            <p class="sample-header-client">
                Cliente:
                @if($sample->client)
                    <a href="{{ route('clients.show', $sample->client) }}" class="sample-header-client-link">
                        {{ $sample->client->company_name ?: $sample->client->first_name . ' ' . $sample->client->last_name }}
                    </a>
                @else
                    <span style="color: #F87171; font-weight: 500;">— Da assegnare —</span>
                @endif
            </p>
        </div>
        <div class="sample-header-right">
            <span class="badge
                @if($sample->status === 'completed') badge-completed
                @elseif($sample->status === 'accepted') badge-accepted
                @elseif($sample->status === 'rejected') badge-rejected
                @else badge-collected
                @endif">
                <span class="badge-dot"></span>
                {{ $sample->status === 'collected' ? 'Prelevato' : ($sample->status === 'accepted' ? 'Accettato' : ($sample->status === 'rejected' ? 'Rifiutato' : 'Completato')) }}
            </span>
            <p class="sample-header-date">Creato il: {{ $sample->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Tab nav --}}
    <div class="sample-tabs">
        <button @click="tab = 'dati'"
            :class="tab === 'dati' ? 'active' : ''"
            class="sample-tab">
            Dati Campione
        </button>
        <button @click="tab = 'file'"
            :class="tab === 'file' ? 'active' : ''"
            class="sample-tab">
            File / Referti ({{ $sample->files->count() }})
        </button>
        @role('admin')
        <button @click="tab = 'storico'"
            :class="tab === 'storico' ? 'active' : ''"
            class="sample-tab">
            Storico Attività ({{ $activities->count() }})
        </button>
        @endrole
    </div>

    {{-- Tab Dati --}}
    <div x-show="tab === 'dati'" class="sample-card">
        <div class="sample-card-header">
            <h3 class="sample-card-title">Informazioni Prelievo e Laboratorio</h3>
        </div>
        <div class="sample-card-body">
            <dl class="sample-dl">
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Tipo Campione</dt>
                    <dd class="sample-dl-value">{{ $sample->sampleType ? $sample->sampleType->name : $sample->sample_type }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Data Prelievo</dt>
                    <dd class="sample-dl-value">{{ \Carbon\Carbon::parse($sample->collected_at)->format('d/m/Y') }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Sito di Prelievo</dt>
                    <dd class="sample-dl-value">{{ $sample->collection_site ?? '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Prelevatore</dt>
                    <dd class="sample-dl-value">{{ $sample->collected_by ?? '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Nom. archiviazione lab</dt>
                    <dd class="sample-dl-value">{{ $sample->lab_archived_by_name ?? '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Tipologia Contenitore</dt>
                    <dd class="sample-dl-value">{{ $sample->containerType ? $sample->containerType->name : '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Stato conservazione</dt>
                    <dd class="sample-dl-value">{{ $sample->conservation_status ?? '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Quantità campione</dt>
                    <dd class="sample-dl-value">{{ $sample->sample_quantity ?? '—' }}</dd>
                </div>
                <div class="sample-dl-item">
                    <dt class="sample-dl-label">Accettato in Laboratorio</dt>
                    <dd class="sample-dl-value">
                        {{ $sample->accepted_at ? \Carbon\Carbon::parse($sample->accepted_at)->format('d/m/Y') : 'In attesa' }}
                    </dd>
                </div>
                <div class="sample-dl-item sample-dl-item--full">
                    <dt class="sample-dl-label">Note</dt>
                    <dd class="sample-dl-value sample-dl-notes">{{ $sample->notes ?: 'Nessuna nota presente.' }}</dd>
                </div>
            </dl>
        </div>
        <div class="sample-card-footer">
            @can('update', $sample)
                <a href="{{ route('samples.edit', $sample) }}" class="btn btn-primary btn-sm">Modifica</a>
            @endcan
            @if(!$sample->archived)
                @can('accept', $sample)
                    <form action="{{ route('samples.accept', $sample) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-secondary btn-sm"
                            onclick="confirmAction(event, 'Confermi di marcare il campione come accettato in laboratorio?')">
                            Accetta in Laboratorio
                        </button>
                    </form>
                @endcan
                @can('reject', $sample)
                    <form action="{{ route('samples.reject', $sample) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="confirmAction(event, 'Confermi di marcare il campione come rifiutato?')">
                            Rifiuta
                        </button>
                    </form>
                @endcan
                @can('complete', $sample)
                    <form action="{{ route('samples.complete', $sample) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-secondary btn-sm"
                            onclick="confirmAction(event, 'Confermi di marcare il campione come completato?')">
                            Completa
                        </button>
                    </form>
                @endcan
                @can('archive', $sample)
                    <form action="{{ route('samples.archive', $sample) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning btn-sm"
                            onclick="confirmAction(event, 'Confermi l\'archiviazione di questo campione e dei suoi file?')">
                            Archivia
                        </button>
                    </form>
                @endcan
            @endif

            @if($sample->archived)
                @can('delete', $sample)
                    <form action="{{ route('samples.destroy', $sample) }}" method="POST" class="inline" style="margin-left: auto;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="confirmAction(event, 'ATTENZIONE: Eliminazione definitiva del campione e dei suoi file fisici. L\'operazione è irreversibile. Procedere?')">
                            Elimina definitivamente
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    {{-- Tab File --}}
    <div x-show="tab === 'file'" x-cloak>
        @if(!$sample->archived)
        <div class="sample-card sample-upload-box">
            <h3 class="sample-card-title" style="margin-bottom:12px;">Carica Nuovo Riferimento</h3>
            <form action="{{ route('samples.files.store', $sample) }}" method="POST" enctype="multipart/form-data" class="sample-upload-form">
                @csrf
                <div class="sample-upload-field">
                    <label class="form-label">Seleziona File (PDF, IMG, ecc.)</label>
                    <input type="file" name="file" class="sample-file-input" required>
                </div>
                <div class="sample-upload-field">
                    <label class="form-label">Tipo Documento</label>
                    <select name="type" class="form-control">
                        <option value="report">Referto / Rapporto di Prova</option>
                        <option value="attachment">Allegato Generico</option>
                        <option value="prescription">Certificato</option>
                        <option value="revised_report">Referto Revisionato</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Carica</button>
            </form>
        </div>
        @else
        <div class="sample-card sample-upload-box" style="background-color: #f8dbdb; border-color: #f5c2c7; color: #842029;">
            <p style="margin: 0;">Il campione è attualmente archiviato. Non è possibile caricare nuovi documenti.</p>
        </div>
        @endif

        <div class="sample-card" style="padding:0;overflow:hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome File</th>
                        <th>Tipo</th>
                        <th>Caricato il</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sample->files as $file)
                    <tr>
                        <td>
                            <a href="{{ route('samples.files.download', [$sample, $file]) }}" class="sample-file-link">
                                {{ $file->original_name }}
                            </a>
                        </td>
                        <td>{{ $file->type === 'report' ? 'Referto / Rapporto di Prova' : ($file->type === 'prescription' ? 'Certificato' : ($file->type === 'revised_report' ? 'Referto Revisionato' : 'Allegato Generico')) }}</td>
                        <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align:right">
                            @can('delete', $file)
                            <form action="{{ route('samples.files.destroy', [$sample, $file]) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="sample-file-delete"
                                    onclick="confirmAction(event, 'Sei sicuro di voler rimuovere questo file?')">
                                    Elimina
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="sample-empty">Nessun file presente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab Storico --}}
    @role('admin')
    <div x-show="tab === 'storico'" x-cloak class="sample-card">
        <div class="sample-card-header">
            <h3 class="sample-card-title">Timeline delle operazioni</h3>
        </div>
        <div class="sample-card-body">
            <ul class="sample-timeline">
                @forelse($activities as $activity)
                <li class="sample-timeline-item">
                    <div class="sample-timeline-line"></div>
                    <div class="sample-timeline-dot"></div>
                    <div class="sample-timeline-body">
                        @php
                            $labels = [
                                'status'          => 'Stato',
                                'notes'           => 'Note',
                                'sample_type'     => 'Tipo Campione (Legacy)',
                                'sample_type_id'  => 'Matrice (Tipo Campione)',
                                'collection_site' => 'Sito di Prelievo',
                                'accepted_at'     => 'Data Accettazione',
                                'collected_at'    => 'Data Prelievo',
                                'collected_by'    => 'Prelevatore',
                                'client_id'       => 'Cliente',
                            ];
                            $valueTranslations = [
                                'collected' => 'Prelevato',
                                'accepted'  => 'Accettato',
                                'completed' => 'Completato',
                                'rejected'  => 'Rifiutato',
                            ];
                            $desc = $activity->description;
                            if ($desc === 'created') {
                                $desc = 'Creazione Campione';
                            } elseif ($desc === 'updated') {
                                $changedKeys = [];
                                if (isset($activity->changes()['attributes'])) {
                                    foreach (array_keys($activity->changes()['attributes']) as $k) {
                                        if (in_array($k, ['updated_at', 'updated_by'])) continue;
                                        $changedKeys[] = $labels[$k] ?? ucfirst(str_replace('_', ' ', $k));
                                    }
                                }
                                $desc = count($changedKeys) > 0
                                    ? 'Modificato: ' . collect($changedKeys)->join(', ', ' e ')
                                    : 'Aggiornamento Campione';
                            } elseif ($desc === 'deleted') {
                                $desc = 'Eliminazione Campione';
                            } else {
                                $desc = ucfirst($desc);
                            }
                        @endphp
                        {{-- Pre-load in memoria i tipi una tantum se non ancora fatto --}}
                        @php \App\Helpers\SampleActivityHelper::resolveSampleTypeDisplay(null, null, $activities); @endphp
                        @php \App\Helpers\SampleActivityHelper::primeClientCache($activities); @endphp
                        <h4 class="sample-timeline-title">{{ $desc }}</h4>
                        <p class="sample-timeline-meta">
                            Eseguito da:
                            <span class="sample-timeline-user">{{ $activity->causer ? $activity->causer->name : 'Sistema' }}</span>
                            &bull; {{ $activity->created_at->format('d/m/Y H:i') }}
                        </p>

                        @if($activity->description === 'updated' && $activity->changes()->isNotEmpty() && isset($activity->changes()['attributes']))
                        <div class="sample-timeline-changes">
                            <ul class="sample-timeline-change-list">
                                @foreach($activity->changes()['attributes'] as $key => $newValue)
                                    @php
                                        if (in_array($key, ['updated_at', 'updated_by'])) continue;
                                        
                                        $rawOld  = $activity->changes()['old'][$key] ?? null;
                                        $rawNew  = $newValue;
                                        
                                        // Overrides specifici
                                        if ($key === 'sample_type_id' || $key === 'sample_type') {
                                            $oldDisp = \App\Helpers\SampleActivityHelper::resolveSingleValue($rawOld);
                                            $newDisp = \App\Helpers\SampleActivityHelper::resolveSingleValue($rawNew);
                                        } elseif ($key === 'client_id') {
                                            $oldDisp = \App\Helpers\SampleActivityHelper::resolveSingleClientValue($rawOld);
                                            $newDisp = \App\Helpers\SampleActivityHelper::resolveSingleClientValue($rawNew);
                                        } else {
                                            $oldDisp = $valueTranslations[$rawOld] ?? ($rawOld ?: 'Nessuno');
                                            $newDisp = $valueTranslations[$rawNew] ?? ($rawNew ?: 'Vuoto');
                                        }
                                        
                                        $keyLabel = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                                    @endphp
                                    <li class="sample-timeline-change-item">
                                        <span class="sample-timeline-change-key">{{ $keyLabel }}:</span>
                                        <span class="sample-timeline-old">{{ $oldDisp }}</span>
                                        <span class="sample-timeline-arrow">&rarr;</span>
                                        <span class="sample-timeline-new">{{ $newDisp }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </li>
                @empty
                <p class="sample-empty">Nessuna attività registrata.</p>
                @endforelse
            </ul>
        </div>
    </div>
    @endrole

    </div> {{-- Fine sample-box --}}
</div>
@endsection