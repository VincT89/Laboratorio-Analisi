@extends('layouts.app')
@section('title', 'Clienti Archiviati')

@section('breadcrumb')
    <span>Archivio Clienti</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Clienti Archiviati</span>
            <a href="{{ route('clients.index') }}" class="archive-back">← Torna ai clienti attivi</a>
        </div>

        @if($clients->isEmpty())
            <div class="table-empty-row">Non ci sono clienti in archivio.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Azienda / Nominativo</th>
                        <th>P.IVA/CF</th>
                        <th>Data Archiviazione</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    @php
                        $clientName = $client->company_name ?: $client->first_name . ' ' . $client->last_name;
                        $confirmMsg = "Ripristinare il cliente " . $clientName . "? Verranno ripristinati anche tutti i suoi campioni.";
                    @endphp
                    <tr class="archived-row">
                        <td>{{ $clientName }}</td>
                        <td>{{ $client->vat_number ?: ($client->tax_code ?: 'N/D') }}</td>
                        <td>{{ $client->archived_at ? \Carbon\Carbon::parse($client->archived_at)->format('d/m/Y H:i') : '' }}</td>
                        <td style="text-align:right">
                            @can('restore', $client)
                            <form action="{{ route('clients.restore', $client) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    onclick="confirmAction(event, '{{ $confirmMsg }}')"
                                    class="archive-action-restore">
                                    Ripristina
                                </button>
                            </form>
                            @endcan

                            @can('delete', $client)
                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="confirmAction(event, 'Attenzione: Eliminazione definitiva e irreversibile del cliente {{ $clientName }}, compresi campioni e file fisici. Procedere?')"
                                    class="archive-action-delete" style="color: #ef4444; margin-left: 10px; font-weight: 500;">
                                    Elimina definitivamente
                                </button>
                            </form>
                            @endcan
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