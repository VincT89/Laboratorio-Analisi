@extends('layouts.app')
@section('title', 'Stati di Conservazione')

@section('breadcrumb')
    <span>Stati di Conservazione</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Stati di Conservazione</span>
            <a href="{{ route('conservation-statuses.create') }}" class="btn btn-primary btn-sm">+ Nuovo Stato</a>
        </div>

        @if($statuses->isEmpty())
            <div class="table-empty-row">Nessun stato configurato. Clicca su Nuovo Stato per iniziare.</div>
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
                    @foreach($statuses as $status)
                    <tr>
                        <td><strong>{{ $status->name }}</strong></td>
                        <td>
                            @if($status->is_active)
                                <span class="badge badge-accepted"><span class="badge-dot"></span>Attivo</span>
                            @else
                                <span class="badge badge-collected" style="background: rgba(255,255,255,0.1); color: #ccc;"><span class="badge-dot" style="background:#888;"></span>Disattivato</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('conservation-statuses.edit', $status) }}" class="row-action" style="margin-right: 15px;">Modifica</a>
                            
                            @if($status->is_active)
                                <form action="{{ route('conservation-statuses.deactivate', $status) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #fca5a5;" onclick="confirmAction(event, 'Disattivare questo stato? Non sarà più proposto per i nuovi campioni.')">
                                        Disattiva
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('conservation-statuses.activate', $status) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #6ee7b7;" onclick="confirmAction(event, 'Riattivare questo stato?')">
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
