@extends('layouts.app')

@section('title', 'Campioni')

@section('breadcrumb')
    <span class="breadcrumb-item active">Lista Campioni</span>
@endsection



@section('content')

    @php
        $currentSort = in_array(request('sort'), ['collected_at', 'acceptance_number'], true)
            ? request('sort')
            : 'collected_at';
        $currentDirection = in_array(request('direction'), ['asc', 'desc'], true)
            ? request('direction')
            : 'desc';
    @endphp

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
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
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
            <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
               class="pill {{ request('status') === 'rejected' ? 'active' : '' }}">Rifiutati</a>
            @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
                <a href="{{ route('samples.index', array_merge(request()->except('status', 'page'), ['status' => 'incomplete'])) }}"
                   class="pill {{ request('status') === 'incomplete' ? 'active' : '' }}">Da completare (Sensibili)</a>
            @endif
        </div>

        <div class="data-table-scroll"
             x-data="{
                 sortMenu: null,
                 menuTop: 0,
                 menuLeft: 0,
                 toggleSortMenu(menu, trigger) {
                     if (this.sortMenu === menu) {
                         this.sortMenu = null;
                         return;
                     }

                     const rect = trigger.getBoundingClientRect();
                     this.menuTop = rect.bottom + 7;
                     this.menuLeft = Math.max(8, Math.min(rect.left - 8, window.innerWidth - 193));
                     this.sortMenu = menu;
                 }
             }"
             @click.window="if (sortMenu && !$event.target.closest('.table-sort-dropdown') && !$event.target.closest('.table-sort-menu')) sortMenu = null"
             @keydown.escape.window="sortMenu = null"
             @resize.window="sortMenu = null"
             @scroll.window="sortMenu = null"
             @scroll="sortMenu = null">
            <table class="data-table samples-index-table">
                <thead>
                    <tr>
                    <th aria-sort="{{ $currentSort === 'acceptance_number' ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                        <div class="table-sort-dropdown">
                            <button type="button"
                                    class="table-sort-trigger {{ $currentSort === 'acceptance_number' ? 'is-active' : '' }}"
                                    @click="toggleSortMenu('acceptance_number', $event.currentTarget)"
                                    :aria-expanded="(sortMenu === 'acceptance_number').toString()"
                                    aria-haspopup="menu"
                                    aria-controls="acceptance-number-sort-menu"
                                    aria-label="Opzioni di ordinamento per numero di accettazione">
                                <span>N. accettazione</span>
                                @if($currentSort === 'acceptance_number')
                                    <span class="table-sort-order">{{ $currentDirection === 'asc' ? 'crescente' : 'decrescente' }}</span>
                                @endif
                                <svg class="table-sort-chevron" viewBox="0 0 10 6" aria-hidden="true">
                                    <path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <template x-teleport="body">
                                <div id="acceptance-number-sort-menu"
                                     class="table-sort-menu"
                                     role="menu"
                                     x-show="sortMenu === 'acceptance_number'"
                                     :style="{ '--sort-menu-top': menuTop + 'px', '--sort-menu-left': menuLeft + 'px' }"
                                     style="display: none;">
                                    <a href="{{ route('samples.index', array_merge(request()->except('page'), ['sort' => 'acceptance_number', 'direction' => 'asc'])) }}"
                                       class="table-sort-option {{ $currentSort === 'acceptance_number' && $currentDirection === 'asc' ? 'is-active' : '' }}"
                                       role="menuitem"
                                       @if($currentSort === 'acceptance_number' && $currentDirection === 'asc') aria-current="true" @endif>
                                        <span>Crescente</span>
                                        <small>Numeri più bassi prima</small>
                                    </a>
                                    <a href="{{ route('samples.index', array_merge(request()->except('page'), ['sort' => 'acceptance_number', 'direction' => 'desc'])) }}"
                                       class="table-sort-option {{ $currentSort === 'acceptance_number' && $currentDirection === 'desc' ? 'is-active' : '' }}"
                                       role="menuitem"
                                       @if($currentSort === 'acceptance_number' && $currentDirection === 'desc') aria-current="true" @endif>
                                        <span>Decrescente</span>
                                        <small>Numeri più alti prima</small>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </th>
                    <th>Cliente</th>
                    <th>Tipologia di campione</th>
                    <th>Note</th>
                    <th aria-sort="{{ $currentSort === 'collected_at' ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                        <div class="table-sort-dropdown">
                            <button type="button"
                                    class="table-sort-trigger {{ $currentSort === 'collected_at' ? 'is-active' : '' }}"
                                    @click="toggleSortMenu('collected_at', $event.currentTarget)"
                                    :aria-expanded="(sortMenu === 'collected_at').toString()"
                                    aria-haspopup="menu"
                                    aria-controls="collected-at-sort-menu"
                                    aria-label="Opzioni di ordinamento per data di prelievo">
                                <span>Data prelievo</span>
                                @if($currentSort === 'collected_at')
                                    <span class="table-sort-order">{{ $currentDirection === 'asc' ? 'crescente' : 'decrescente' }}</span>
                                @endif
                                <svg class="table-sort-chevron" viewBox="0 0 10 6" aria-hidden="true">
                                    <path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <template x-teleport="body">
                                <div id="collected-at-sort-menu"
                                     class="table-sort-menu"
                                     role="menu"
                                     x-show="sortMenu === 'collected_at'"
                                     :style="{ '--sort-menu-top': menuTop + 'px', '--sort-menu-left': menuLeft + 'px' }"
                                     style="display: none;">
                                    <a href="{{ route('samples.index', array_merge(request()->except('page'), ['sort' => 'collected_at', 'direction' => 'asc'])) }}"
                                       class="table-sort-option {{ $currentSort === 'collected_at' && $currentDirection === 'asc' ? 'is-active' : '' }}"
                                       role="menuitem"
                                       @if($currentSort === 'collected_at' && $currentDirection === 'asc') aria-current="true" @endif>
                                        <span>Crescente</span>
                                        <small>Meno recenti prima</small>
                                    </a>
                                    <a href="{{ route('samples.index', array_merge(request()->except('page'), ['sort' => 'collected_at', 'direction' => 'desc'])) }}"
                                       class="table-sort-option {{ $currentSort === 'collected_at' && $currentDirection === 'desc' ? 'is-active' : '' }}"
                                       role="menuitem"
                                       @if($currentSort === 'collected_at' && $currentDirection === 'desc') aria-current="true" @endif>
                                        <span>Decrescente</span>
                                        <small>Più recenti prima</small>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </th>
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
                        <td class="sample-notes-cell">
                            @if($row->notesFull())
                                <span class="sample-notes-preview" title="{{ $row->notesFull() }}">{{ $row->notesPreview() }}</span>
                            @else
                                <span class="table-empty-cell">{{ $row->notesPreview() }}</span>
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
                                @elseif($row->sample->status === 'rejected')
                                    <span class="badge badge-rejected"><span class="badge-dot"></span>Rifiutato</span>
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
                        <td colspan="8" class="table-empty-row">Nessun campione trovato</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

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
