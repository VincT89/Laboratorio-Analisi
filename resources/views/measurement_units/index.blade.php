@extends('layouts.app')
@section('title', 'Unità di Misura')

@section('breadcrumb')
    <span>Unità di Misura</span>
@endsection

@section('content')
<div class="archive-wrap">
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Unità di Misura</span>
            <a href="{{ route('measurement-units.create') }}" class="btn btn-primary btn-sm">+ Nuova Unità</a>
        </div>

        @if($units->isEmpty())
            <div class="table-empty-row">Nessuna unità configurata. Clicca su Nuova Unità per iniziare.</div>
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
                    @foreach($units as $unit)
                    <tr>
                        <td><strong>{{ $unit->name }}</strong></td>
                        <td>
                            @if($unit->is_active)
                                <span class="badge badge-accepted"><span class="badge-dot"></span>Attivo</span>
                            @else
                                <span class="badge badge-collected" style="background: rgba(255,255,255,0.1); color: #ccc;"><span class="badge-dot" style="background:#888;"></span>Disattivato</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('measurement-units.edit', $unit) }}" class="row-action" style="margin-right: 15px;">Modifica</a>
                            
                            @if($unit->is_active)
                                <form action="{{ route('measurement-units.deactivate', $unit) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #fca5a5;" onclick="confirmAction(event, 'Disattivare questa unità? Non sarà più proposta per i nuovi campioni.')">
                                        Disattiva
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('measurement-units.activate', $unit) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="row-action" style="color: #6ee7b7;" onclick="confirmAction(event, 'Riattivare questa unità?')">
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
