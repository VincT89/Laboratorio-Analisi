@extends('layouts.app')
@section('title', 'Clienti')

@section('breadcrumb')
    <span class="breadcrumb-item active">Lista Clienti</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">

        <div class="table-toolbar" style="margin-bottom: 16px;">
            <span class="table-toolbar-title" style="margin-right:auto">Lista clienti</span>
            <form action="{{ route('clients.index') }}" method="GET">
                <div class="table-search" style="min-width:300px;">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                        <circle cx="5.5" cy="5.5" r="4" stroke="#AAA" stroke-width="1.2"/>
                        <path d="M9 9l2.5 2.5" stroke="#AAA" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cerca azienda, nome, cognome..."
                           oninput="clearTimeout(this.timer); this.timer=setTimeout(() => { this.form.submit(); }, 400);">
                </div>
            </form>
        </div>

        @if($clients->isEmpty())
            <div class="table-empty-row">
                @if(request('search'))
                    Nessun cliente trovato con i filtri attuali.
                @else
                    Non ci sono clienti. Inizia creandone uno nuovo!
                @endif
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Azienda / Nominativo</th>
                        <th>Contatti</th>
                        <th>Città</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>
                            <a href="{{ route('clients.show', $client) }}" class="client-name-link">
                                {{ $client->company_name ?: $client->first_name . ' ' . $client->last_name }}
                            </a>
                            <div class="client-type">
                                {{ $client->type === 'company' ? 'Azienda' : 'Privato' }}
                                {{ $client->vat_number ? ' — P.IVA: ' . $client->vat_number : '' }}
                            </div>
                        </td>
                        <td>
                            <div class="client-name">{{ $client->email ?? '---' }}</div>
                            <div class="client-type">{{ $client->phone ?? '---' }}</div>
                        </td>
                        <td>{{ $client->city ?? '---' }}{{ $client->province ? ' ('.$client->province.')' : '' }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('samples.create', ['client_id' => $client->id]) }}"
                               class="client-new-sample-btn">+ Campione</a>
                            <a href="{{ route('clients.show', $client) }}" class="row-action">Apri →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection