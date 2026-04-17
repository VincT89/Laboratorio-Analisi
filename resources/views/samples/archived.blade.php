@extends('layouts.app')
@section('title', 'Campioni Archiviati')

@section('breadcrumb')
    <span>Archivio Campioni</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Campioni Archiviati</span>
            <a href="{{ route('samples.index') }}" class="archive-back">← Torna ai campioni attivi</a>
        </div>

        @if($samples->isEmpty())
            <div class="table-empty-row">Non ci sono campioni archiviati.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Data Archiviazione</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($samples as $row)
                    <tr class="archived-row">
                        <td><span class="sample-code">{{ $row->sample->code }}</span></td>
                        <td>
                            @if($row->isMasked())
                                <span style="color: #6b7280;">{{ $row->clientName() }}</span>
                            @else
                                {{ $row->clientName() }}
                            @endif
                        </td>
                        <td>
                            @if($row->isMasked())
                                <span style="color: #6b7280;">{{ $row->sampleTypeName() }}</span>
                            @else
                                {{ $row->sampleTypeName() }}
                            @endif
                        </td>
                        <td>{{ $row->sample->archived_at ? \Carbon\Carbon::parse($row->sample->archived_at)->format('d/m/Y') : '' }}</td>
                        <td style="text-align:right">
                            @if(!$row->isMasked())
                                <a href="{{ route('samples.show', $row->sample) }}" class="archive-action-view">Visualizza</a>
                            @endif
                            
                            @can('restore', $row->sample)
                            <form action="{{ route('samples.restore', $row->sample) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    onclick="confirmAction(event, 'Ripristinare il campione {{ $row->sample->code }}? Tornerà tra i campioni attivi.')"
                                    class="archive-action-restore">
                                    Ripristina
                                </button>
                            </form>
                            @endcan

                            @can('delete', $row->sample)
                            <form action="{{ route('samples.destroy', $row->sample) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="confirmAction(event, 'Attenzione: Eliminazione definitiva e irreversibile del campione {{ $row->sample->code }} e dei relativi file fisici. Procedere?')"
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
                {{ $samples->links() }}
            </div>
        @endif
    </div>
</div>
@endsection