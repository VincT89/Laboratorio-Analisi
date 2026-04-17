@extends('layouts.app')

@section('title', 'Campioni')

@section('breadcrumb')
    <span class="breadcrumb-item active">Lista Campioni</span>
@endsection



@section('content')

    {{-- Statistiche --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-accent" style="background:#6B7280"></div>
            <div class="stat-card-label">Totali attivi</div>
            <div class="stat-card-value">{{ $metrics['totalActive'] }}</div>
            <div class="stat-card-footer">campioni attivi</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-accent" style="background:#F59E0B"></div>
            <div class="stat-card-label">Prelevati</div>
            <div class="stat-card-value">{{ $metrics['totalCollected'] }}</div>
            <div class="stat-card-footer">in attesa</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-accent" style="background:#3B82F6"></div>
            <div class="stat-card-label">Accettati</div>
            <div class="stat-card-value">{{ $metrics['totalAccepted'] }}</div>
            <div class="stat-card-footer">in lavorazione</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-accent" style="background:#22C55E"></div>
            <div class="stat-card-label">Completati</div>
            <div class="stat-card-value">{{ $metrics['totalCompleted'] }}</div>
            <div class="stat-card-footer">chiusi</div>
        </div>
    </div>

    {{-- Tabella campioni --}}
    <div class="table-wrap">
        <div class="table-toolbar">
            <span class="table-toolbar-title">Lista campioni</span>

            <form method="GET" action="{{ route('samples.index') }}" style="margin-right:auto; margin-left: 20px;">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="table-search" style="min-width:250px;">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                        <circle cx="5.5" cy="5.5" r="4" stroke="#AAA" stroke-width="1.2"/>
                        <path d="M9 9l2.5 2.5" stroke="#AAA" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cerca codice o cliente..."
                           oninput="clearTimeout(this.timer); this.timer=setTimeout(() => { this.form.submit(); }, 400);">
                </div>
            </form>

            <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), [])) }}"
               class="pill {{ !request('status') ? 'active' : '' }}">Tutti</a>
            <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'collected'])) }}"
               class="pill {{ request('status') === 'collected' ? 'active' : '' }}">Prelevati</a>
            <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'accepted'])) }}"
               class="pill {{ request('status') === 'accepted' ? 'active' : '' }}">Accettati</a>
            <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}"
               class="pill {{ request('status') === 'completed' ? 'active' : '' }}">Completati</a>
            @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
                <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'incomplete'])) }}"
                   class="pill {{ request('status') === 'incomplete' ? 'active' : '' }}">Da completare (Sensibili)</a>
            @endif
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Codice</th>
                    <th>Cliente</th>
                    <th>Tipo campione</th>
                    <th>Data prelievo</th>
                    <th>Stato</th>
                    <th>File</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($samples as $row)
                    <tr {!! !$row->isMasked() ? 'onclick="window.location=\''.route('samples.show', $row->sample).'\'"' : 'style="cursor: default;"' !!}>
                        <td><span class="sample-code">{{ $row->sample->code }}</span></td>
                        <td>
                            @if($row->isMasked())
                                <div class="client-name" style="color: #6b7280;">{{ $row->clientName() }}</div>
                            @else
                                <div class="client-name">{{ $row->clientName() }}</div>
                                @if($row->clientType())
                                    <div class="client-type">{{ $row->clientType() }}</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($row->isMasked())
                                <span style="color: #6b7280;">{{ $row->sampleTypeName() }}</span>
                            @else
                                {{ $row->sampleTypeName() }}
                            @endif
                        </td>
                        <td>{{ $row->sample->collected_at->format('d/m/Y') }}</td>
                        <td>
                            @if($row->isMasked())
                                <span class="badge" style="background: rgba(107, 114, 128, 0.2); color: #9CA3AF;"><span class="badge-dot" style="background: #9CA3AF;"></span>Sensibile</span>
                            @else
                                @if($row->sample->status === 'collected')
                                    <span class="badge badge-collected"><span class="badge-dot"></span>Prelevato</span>
                                @elseif($row->sample->status === 'accepted')
                                    <span class="badge badge-accepted"><span class="badge-dot"></span>Accettato</span>
                                @else
                                    <span class="badge badge-completed"><span class="badge-dot"></span>Completato</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($row->isMasked())
                                <span class="table-empty-cell">{{ $row->filesDisplay() }}</span>
                            @else
                                @if($row->sample->files_count > 0)
                                    <span class="file-count">{{ $row->filesDisplay() }}</span>
                                @else
                                    <span class="table-empty-cell">{{ $row->filesDisplay() }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if(!$row->isMasked())
                                <a href="{{ route('samples.show', $row->sample) }}" class="row-action">Apri →</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-empty-row">Nessun campione trovato</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($samples->hasPages())
            <div class="pagination">
                <span>{{ $samples->firstItem() }}–{{ $samples->lastItem() }} di {{ $samples->total() }}</span>
                <div class="pagination-links">
                    {{ $samples->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>

@endsection