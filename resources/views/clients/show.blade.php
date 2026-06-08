@extends('layouts.app')
@section('title', 'Dettaglio Cliente: ' . ($client->company_name ?: $client->first_name . ' ' . $client->last_name))

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="breadcrumb-item">Clienti</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">{{ $client->company_name ?: $client->first_name . ' ' . $client->last_name }}</span>
@endsection

@section('content')
<div class="client-detail" x-data="{ tab: 'campioni' }">

    {{-- Box unico --}}
    <div class="client-box">

        {{-- Header cliente --}}
        <div class="client-box-header">
            <div>
                <h1 class="client-show-name">{{ $client->company_name ?: $client->first_name . ' ' . $client->last_name }}</h1>
                <div class="client-show-meta">
                    <span class="client-show-type">{{ $client->type === 'company' ? 'Azienda' : 'Privato' }}</span>
                    <span class="client-show-meta-sep">•</span>
                    <span>P.IVA/CF: <strong>{{ $client->vat_number ?: ($client->tax_code ?: 'N/D') }}</strong></span>
                    <span class="client-show-meta-sep">•</span>
                    <a href="mailto:{{ $client->email }}" class="client-show-link">{{ $client->email ?? 'Nessuna Email' }}</a>
                    <span class="client-show-meta-sep">•</span>
                    <a href="tel:{{ $client->phone }}" class="client-show-link">{{ $client->phone ?? 'Nessun Telefono' }}</a>
                </div>
            </div>
            <a href="{{ route('samples.create', ['client_id' => $client->id]) }}" class="btn btn-primary">
                + Nuovo Campione
            </a>
        </div>

        {{-- Tab nav --}}
        <div class="client-box-tabs">
            <button @click="tab = 'campioni'"
                :class="tab === 'campioni' ? 'active' : ''"
                class="sample-tab">
                Campioni Associati ({{ $client->samples->count() }})
            </button>
            <button @click="tab = 'dati'"
                :class="tab === 'dati' ? 'active' : ''"
                class="sample-tab">
                Dettagli Anagrafica
            </button>
        </div>

        {{-- Tab campioni --}}
        <div x-show="tab === 'campioni'">
            @if($client->samples->isEmpty())
                <div class="table-empty-row">Nessun campione presente per questo cliente.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Prelievo</th>
                            <th>Tipo / Sito</th>
                            <th>Stato</th>
                            <th style="text-align:right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->samples as $sample)
                        @php
                            $isStaffMasked = $sample->isSensitive() && !\Illuminate\Support\Facades\Auth::user()->isAdmin();
                        @endphp
                        <tr>
                            <td>
                                @if(!$isStaffMasked)
                                    <a href="{{ route('samples.show', $sample) }}" class="sample-code">{{ $sample->code }}</a>
                                @else
                                    <span class="sample-code" style="color: #9CA3AF;">{{ $sample->code }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size:13px;color:#18243A;">{{ \Carbon\Carbon::parse($sample->collected_at)->format('d/m/Y') }}</div>
                                @if(!$isStaffMasked)
                                    <div style="font-size:11px;color:#7A88A0;">{{ $sample->collected_by ?? '—' }}</div>
                                @endif
                            </td>
                            <td>
                                @if($isStaffMasked)
                                    <div style="font-size:13px;color:#6b7280;">******</div>
                                @else
                                    <div style="font-size:13px;color:#18243A;">{{ $sample->sampleType ? $sample->sampleType->name : $sample->sample_type }}</div>
                                    <div style="font-size:11px;color:#7A88A0;">{{ $sample->collection_site ?? '—' }}</div>
                                @endif
                            </td>
                            <td>
                                @if($isStaffMasked)
                                    <span class="badge" style="background: rgba(107, 114, 128, 0.2); color: #9CA3AF;"><span class="badge-dot" style="background: #9CA3AF;"></span>Sensibile</span>
                                @else
                                    @if($sample->status === 'collected')
                                        <span class="badge badge-collected"><span class="badge-dot"></span>Prelevato</span>
                                    @elseif($sample->status === 'accepted')
                                        <span class="badge badge-accepted"><span class="badge-dot"></span>Accettato</span>
                                    @elseif($sample->status === 'rejected')
                                        <span class="badge badge-rejected"><span class="badge-dot"></span>Rifiutato</span>
                                    @else
                                        <span class="badge badge-completed"><span class="badge-dot"></span>Completato</span>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align:right">
                                @if(!$isStaffMasked)
                                    <a href="{{ route('samples.show', $sample) }}" class="row-action">Dettaglio →</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Tab dati anagrafici --}}
        <div x-show="tab === 'dati'" x-cloak style="padding:20px 24px;">
            <div class="client-detail-grid">
                <div>
                    <div class="client-detail-section-title">Sede e Indirizzo</div>
                    <div class="client-detail-label">Indirizzo completo</div>
                    <div class="client-detail-value">
                        {{ $client->address ?: 'N/D' }}<br>
                        {{ $client->postal_code }} {{ $client->city }} {{ $client->province ? '('.$client->province.')' : '' }}<br>
                        {{ $client->country }}
                    </div>
                </div>
                <div>
                    <div class="client-detail-section-title">Fatturazione</div>
                    <div class="client-detail-label">Partita IVA</div>
                    <div class="client-detail-value">{{ $client->vat_number ?: '---' }}</div>
                    <div class="client-detail-label" style="margin-top:12px">Codice Fiscale</div>
                    <div class="client-detail-value">{{ $client->tax_code ?: '---' }}</div>
                    <div class="client-detail-label" style="margin-top:12px">Codice SDI</div>
                    <div class="client-detail-value">{{ $client->sdi_code ?: '---' }}</div>
                </div>
                <div>
                    <div class="client-detail-section-title">Recapiti</div>
                    <div class="client-detail-label">Telefono</div>
                    <div class="client-detail-value">{{ $client->phone ?: '---' }}</div>
                    <div class="client-detail-label" style="margin-top:12px">PEC</div>
                    <div class="client-detail-value">{{ $client->pec ?: '---' }}</div>
                </div>
                @if($client->notes)
                <div class="client-detail-notes">
                    <div class="client-detail-label">Altre Informazioni / Note</div>
                    <div class="client-detail-notes-body">{{ $client->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Footer azioni --}}
        <div class="client-show-footer">
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-secondary btn-sm">Modifica</a>
            @endcan
            
            @if(!$client->archived)
                @can('archive', $client)
                    <form action="{{ route('clients.archive', $client) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning btn-sm"
                            onclick="confirmAction(event, 'Confermi l\'archiviazione di questo cliente e tutti i suoi campioni?')">
                            Archivia
                        </button>
                    </form>
                @endcan
            @endif

            @if($client->archived)
                @can('delete', $client)
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" style="margin-left: auto;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="confirmAction(event, 'ATTENZIONE: Stai per eliminare definitivamente questo cliente, tutti i suoi campioni e tutti i file caricati. Questa azione è IRREVERSIBILE. Confermi?')">
                            Elimina definitivamente
                        </button>
                    </form>
                @endcan
            @endif
        </div>

    </div>
</div>
@endsection