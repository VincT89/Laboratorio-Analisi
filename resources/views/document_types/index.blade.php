@extends('layouts.app')
@section('title', 'Tipi di Documento')

@section('breadcrumb')
    <span>Tipi di Documento</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Tipi di Documento</span>
            <a href="{{ route('document-types.create') }}" class="btn btn-primary btn-sm">Nuovo Tipo</a>
        </div>

        @if($types->isEmpty())
            <div class="table-empty-row">Nessun tipo di documento configurato. Seleziona Nuovo Tipo per iniziare.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ordine</th>
                        <th>Nome</th>
                        <th>Stato</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $type)
                    <tr>
                        <td>{{ $type->sort_order }}</td>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td>
                            @if($type->is_active)
                                <span class="badge badge-accepted"><span class="badge-dot"></span>Attivo</span>
                            @else
                                <span class="badge badge-collected" style="background: rgba(255,255,255,0.1); color: #ccc;"><span class="badge-dot" style="background:#888;"></span>Disattivato</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('document-types.edit', $type) }}" class="row-action" style="margin-right: 15px;">Modifica</a>

                            @if($type->is_active)
                                <form action="{{ route('document-types.deactivate', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #fca5a5;" onclick="confirmAction(event, 'Disattivare questo tipo? Non sarà più disponibile per i nuovi caricamenti.')">
                                        Disattiva
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('document-types.activate', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #6ee7b7;" onclick="confirmAction(event, 'Riattivare questo tipo di documento?')">
                                        Attiva
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
