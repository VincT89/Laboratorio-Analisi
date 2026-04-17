@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <span>Dashboard</span>
@endsection

@section('content')
<div class="dashboard-wrap">

    {{-- Riga 1: Stat cards --}}
    <div class="dash-stats">

        <div class="dash-stat-card">
            <div class="dash-stat-accent" style="background:#078586"></div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Campioni attivi</div>
                <div class="dash-stat-value">{{ $totalActive }}</div>
                <div class="dash-stat-foot">totale non archiviati</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-accent" style="background:#F59E0B"></div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Da accettare</div>
                <div class="dash-stat-value">{{ $totalCollected }}</div>
                <div class="dash-stat-foot">in attesa accettazione</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-accent" style="background:#3B82F6"></div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">In lavorazione</div>
                <div class="dash-stat-value">{{ $totalAccepted }}</div>
                <div class="dash-stat-foot">campioni accettati</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-accent" style="background:#22C55E"></div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Completati</div>
                <div class="dash-stat-value">{{ $totalCompleted }}</div>
                <div class="dash-stat-foot">campioni chiusi</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-accent" style="background:#8B5CF6"></div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Clienti attivi</div>
                <div class="dash-stat-value">{{ $totalClients }}</div>
                <div class="dash-stat-foot">non archiviati</div>
            </div>
        </div>

    </div>

    {{-- Riga 2: Calendario + Grafico --}}
    <div class="dash-row">

        {{-- Calendario prelievi --}}
        <div class="dash-card">
            <div class="dash-card-header">
                <span class="dash-card-title">Prelievi questo mese</span>
                <span class="dash-card-sub">{{ ucfirst(now()->translatedFormat('F Y')) }}</span>
            </div>
            <div class="dash-calendar">
                @foreach(['L','M','M','G','V','S','D'] as $d)
                    <div class="dash-cal-head">{{ $d }}</div>
                @endforeach

                @php
                    $firstDay = now()->startOfMonth()->dayOfWeek;
                    $firstDay = $firstDay === 0 ? 6 : $firstDay - 1;
                    $daysInMonth = now()->daysInMonth;
                @endphp

                @for($i = 0; $i < $firstDay; $i++)
                    <div class="dash-cal-cell dash-cal-cell--empty"></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $count = $calendarData[$day] ?? 0;
                        $isToday = $day === now()->day;
                    @endphp
                    <div class="dash-cal-cell {{ $isToday ? 'dash-cal-cell--today' : '' }} {{ $count > 0 && !$isToday ? 'dash-cal-cell--has-samples' : '' }}"
                        @if($count > 0) title="{{ $count }} prelievo/i" @endif>
                        <span class="dash-cal-day">{{ $day }}</span>
                        @if($count > 0)
                            <span class="dash-cal-dot"></span>
                        @endif
                    </div>
                @endfor
            </div>

            <div class="dash-cal-legend">
                <div class="dash-cal-legend-item">
                    <span class="dash-cal-dot" style="display:inline-block"></span>
                    <span>giorno con prelievi</span>
                </div>
                <div class="dash-cal-legend-item">
                    <span class="dash-cal-today-pill">oggi</span>
                </div>
            </div>

            @if($upcomingSamples->isNotEmpty())
            <div style="margin-top:12px; border-top:0.5px solid #E5E4E0; padding-top:12px;">
                <div class="dash-card-title" style="margin-bottom:8px;">Prelievi recenti</div>
                @foreach($upcomingSamples as $row)
                <div class="dash-mini-row">
                    <span class="dash-code" style="font-size:11px; {{ $row->isMasked() ? 'color:#9CA3AF;' : '' }}">{{ $row->sample->code }}</span>
                    <span style="font-size:11px;color:#AAA;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin:0 8px;">
                        {{ $row->clientName() }}
                    </span>
                    <span style="font-size:11px;color:#BBB;">{{ $row->sample->collected_at->format('d/m') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Grafico campioni per mese --}}
        <div class="dash-card" style="display:flex; flex-direction:column;">
            <div class="dash-card-header" style="flex-shrink:0;">
                <span class="dash-card-title">Campioni per mese</span>
                <span class="dash-card-sub">ultimi 6 mesi</span>
            </div>
            <div style="position:relative; flex:1;">
                <canvas id="dashChart"
                    role="img"
                    aria-label="Grafico a barre dei campioni creati negli ultimi 6 mesi">
                    Dati campioni ultimi 6 mesi.
                </canvas>
            </div>
            <div style="display:flex; gap:16px; margin-top:12px; font-size:12px; color:#888; flex-shrink:0;">
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:#078586;display:inline-block;"></span>
                    Nuovi campioni
                </span>
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:#22C55E;display:inline-block;"></span>
                    Completati
                </span>
            </div>
        </div>

    </div>

    @if(\Illuminate\Support\Facades\Auth::user()->isAdmin() && isset($sensitiveIncompleteCount))
    <div style="margin-bottom: 24px;">
        <div class="dash-card" style="border: 1px solid rgba(239, 68, 68, 0.3);">
            <div class="dash-card-header" style="border-bottom: 1px solid rgba(239, 68, 68, 0.1);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dash-card-title" style="color: #F87171;">Analisi Sensibili da Completare ({{ $sensitiveIncompleteCount }})</span>
                </div>
                <a href="{{ route('samples.index', ['status' => 'incomplete']) }}" class="dash-card-link" style="color: #F87171;">Vedi tutti →</a>
            </div>
            
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Data Preregistrazione</th>
                            <th>Prelevato il</th>
                            <th>Creato da</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sensitiveIncompleteSamples as $row)
                        <tr onclick="window.location='{{ route('samples.edit', $row->sample) }}'" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.05)'" onmouseout="this.style.background='transparent'">
                            <td><span class="dash-code">{{ $row->sample->code }}</span></td>
                            <td style="color:#AAA">{{ $row->sample->created_at->format('d/m/Y H:i') }}</td>
                            <td style="color:#AAA">{{ $row->sample->collected_at->format('d/m/Y') }}</td>
                            <td style="color:#AAA">{{ $row->sample->createdBy?->name ?? 'Staff' }}</td>
                            <td style="text-align: right;">
                                <span style="background: rgba(239, 68, 68, 0.1); color: #F87171; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; border: 1px solid rgba(239, 68, 68, 0.2);">Completa →</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="dash-empty" style="padding: 24px;">Nessuna analisi sensibile da completare</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Riga 3: Campioni + Avvisi + Attività (3 colonne) --}}
    <div class="dash-row-3">

        {{-- Campioni da lavorare --}}
        <div class="dash-card">
            <div class="dash-card-header">
                <span class="dash-card-title">Campioni da lavorare</span>
                <a href="{{ route('samples.index') }}" class="dash-card-link">Vedi tutti →</a>
            </div>
            <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Cliente</th>
                        <th>Stato</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSamples as $row)
                    <tr {!! !$row->isMasked() ? 'onclick="window.location=\''.route('samples.show', $row->sample).'\'"' : 'style="cursor: default;"' !!}>
                        <td><span class="dash-code" style="{{ $row->isMasked() ? 'color:#9CA3AF;' : '' }}">{{ $row->sample->code }}</span></td>
                        <td class="dash-client" style="{{ $row->isMasked() ? 'color:#6b7280;' : '' }}">
                            {{ $row->clientName() }}
                        </td>
                        <td>
                            @if($row->isMasked())
                                <span class="dash-badge" style="background: rgba(107, 114, 128, 0.2); color: #9CA3AF;">Sensibile</span>
                            @else
                                @if($row->sample->status === 'collected')
                                    <span class="dash-badge dash-badge--amber">Prelevato</span>
                                @elseif($row->sample->status === 'accepted')
                                    <span class="dash-badge dash-badge--blue">Accettato</span>
                                @else
                                    <span class="dash-badge dash-badge--green">Completato</span>
                                @endif
                            @endif
                        </td>
                        <td class="dash-arrow">
                            @if(!$row->isMasked())
                                →
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="dash-empty">Nessun campione attivo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Avvisi --}}
        <div class="dash-card">
            <div class="dash-card-header">
                <span class="dash-card-title">Avvisi</span>
            </div>

            <div class="dash-scroll">
            @if($overdueCollected > 0)
            <div class="dash-alert dash-alert--amber">
                <div class="dash-alert-icon">!</div>
                <div>
                    <div class="dash-alert-text">{{ $overdueCollected }} campion{{ $overdueCollected === 1 ? 'e' : 'i' }} in attesa da oltre 48h</div>
                    <div class="dash-alert-sub">non ancora accettati</div>
                </div>
            </div>
            @endif

            @if($samplesWithoutFiles > 0)
            <div class="dash-alert dash-alert--red">
                <div class="dash-alert-icon">!</div>
                <div>
                    <div class="dash-alert-text">{{ $samplesWithoutFiles }} campion{{ $samplesWithoutFiles === 1 ? 'e completato' : 'i completati' }} senza file</div>
                    <div class="dash-alert-sub">nessun referto allegato</div>
                </div>
            </div>
            @endif

            @if($newClientsThisMonth > 0)
            <div class="dash-alert dash-alert--green">
                <div class="dash-alert-icon">+</div>
                <div>
                    <div class="dash-alert-text">{{ $newClientsThisMonth }} nuov{{ $newClientsThisMonth === 1 ? 'o cliente' : 'i clienti' }} questo mese</div>
                    <div class="dash-alert-sub">aggiunti nel mese corrente</div>
                </div>
            </div>
            @endif

            @if($filesUploadedToday > 0)
            <div class="dash-alert dash-alert--blue">
                <div class="dash-alert-icon">i</div>
                <div>
                    <div class="dash-alert-text">{{ $filesUploadedToday }} file caricati oggi</div>
                    <div class="dash-alert-sub">referti e allegati</div>
                </div>
            </div>
            @endif

            @if($overdueCollected === 0 && $samplesWithoutFiles === 0 && $newClientsThisMonth === 0 && $filesUploadedToday === 0)
            <div class="dash-empty" style="padding:8px 0">Nessun avviso attivo</div>
            @endif
            </div>
        </div>

        {{-- Attività recente — solo admin --}}
        @if(auth()->user()->isAdmin())
        <div class="dash-card">
            <div class="dash-card-header">
                <span class="dash-card-title">Attività recente</span>
            </div>
            <div class="dash-scroll">
            @forelse($recentActivities as $activity)
            <div class="dash-activity">
                <div class="dash-activity-dot" style="background:
                    @if(str_contains($activity->description, 'created') || str_contains($activity->description, 'Caricato')) #078586
                    @elseif(str_contains($activity->description, 'completat')) #22C55E
                    @elseif(str_contains($activity->description, 'archiviato')) #6B7280
                    @else #3B82F6
                    @endif
                "></div>
                <div class="dash-activity-body">
                    <div class="dash-activity-text">
                        @php
                            $desc = $activity->description;
                            $subject = $activity->subject_type ? class_basename($activity->subject_type) : '';
                            $map = ['created' => 'Creato', 'updated' => 'Aggiornato', 'deleted' => 'Eliminato'];
                            $label = $map[$desc] ?? $desc;
                            $subjectLabel = match($subject) {
                                'Sample'     => 'campione',
                                'Client'     => 'cliente',
                                'SampleFile' => 'file',
                                'User'       => 'utente',
                                default      => strtolower($subject),
                            };
                            $name = $activity->subject?->code
                                 ?? $activity->subject?->company_name
                                 ?? $activity->subject?->name
                                 ?? $activity->subject?->original_name
                                 ?? '';
                        @endphp
                        {{ $label }}{{ $subjectLabel ? ' ' . $subjectLabel : '' }}{{ $name ? ': ' . $name : '' }}
                    </div>
                    <div class="dash-activity-meta">
                        {{ $activity->causer?->name ?? 'Sistema' }} · {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div class="dash-empty" style="padding:8px 0">Nessuna attività recente</div>
            @endforelse
            </div>
        </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('dashChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Nuovi',
                data: {!! json_encode($chartCreated) !!},
                backgroundColor: '#078586',
                borderRadius: 4,
                borderSkipped: false,
            },
            {
                label: 'Completati',
                data: {!! json_encode($chartCompleted) !!},
                backgroundColor: '#22C55E',
                borderRadius: 4,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 12 }, color: '#888', autoSkip: false }
            },
            y: {
                grid: { color: 'rgba(128,128,128,0.1)' },
                ticks: { font: { size: 12 }, color: '#888', precision: 0 },
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush