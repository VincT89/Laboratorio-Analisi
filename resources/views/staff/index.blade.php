@extends('layouts.app')
@section('title', 'Gestione Staff')

@section('breadcrumb')
    <span class="breadcrumb-item active">Gestione Staff</span>
@endsection

@section('content')
<div class="table-wrap" style="margin-top:24px;">
    <div class="table-toolbar">
        <span class="table-toolbar-title">Elenco Staff</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Ruolo</th>
                <th>Data Modifica</th>
                <th style="text-align:right">Azioni</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->isAdmin())
                        <span class="staff-badge staff-badge--admin">Admin</span>
                    @else
                        <span class="staff-badge staff-badge--staff">Staff</span>
                    @endif
                </td>
                <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                <td style="text-align:right">
                    <div class="staff-actions">
                        <a href="{{ route('staff.edit', $user) }}" class="staff-action-edit">Modifica</a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('staff.destroy', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="staff-action-delete"
                                    onclick="confirmAction(event, 'Cancellando questo utente, rimuoverai ogni suo accesso al sistema, sei sicuro?')">
                                    Elimina
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($users->hasPages())
        <div class="pagination">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection