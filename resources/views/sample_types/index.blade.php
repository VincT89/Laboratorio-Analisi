@extends('layouts.app')
@section('title', 'Tipi di Campione')

@section('breadcrumb')
    <span>Tipi di Campione</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Tipi di Campione</span>
            <a href="{{ route('sample-types.create') }}" class="btn btn-primary btn-sm">+ Nuovo Tipo</a>
        </div>

        @if($types->isEmpty())
            <div class="table-empty-row">Nessun tipo di campione configurato. Clicca su Nuovo Tipo per iniziare.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Stato</th>
                        <th style="text-align:right">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $type)
                    <tr>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td>
                            @if($type->is_active)
                                <span class="badge badge-accepted"><span class="badge-dot"></span>Attivo</span>
                            @else
                                <span class="badge badge-collected" style="background: rgba(255,255,255,0.1); color: #ccc;"><span class="badge-dot" style="background:#888;"></span>Disattivato</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('sample-types.edit', $type) }}" class="row-action" style="margin-right: 15px;">Modifica</a>
                            
                            @if($type->is_active)
                                <form action="{{ route('sample-types.deactivate', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #fca5a5;" onclick="confirmAction(event, 'Disattivare questo tipo di campione? Non sarà più selezionabile per i nuovi campioni.')">
                                        Disattiva
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('sample-types.activate', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #6ee7b7;" onclick="confirmAction(event, 'Riattivare questo tipo di campione?')">
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
