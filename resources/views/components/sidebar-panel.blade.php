{{-- Sidebar Panel — navigazione principale --}}

<div class="panel" id="sidebar-panel">

    {{-- Logo --}}
    <div class="panel-head">
        <div class="panel-logo-mark">
            <img src="{{ asset('images/logo.png') }}" alt="AER Consulting Logo" class="panel-logo-img">
        </div>
        <span class="panel-title">A.E.R <span style="font-size: 0.7em; font-weight: 500; opacity: 0.8;">Consulting</span></span>
        <button class="panel-toggle" onclick="togglePanel()" title="Chiudi">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M9 3L5 7l4 4" stroke="currentColor"
                      stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    {{-- Navigazione --}}
    <nav class="panel-nav">

        <div class="panel-nav-group">
            <a href="{{ route('dashboard') }}"
               class="panel-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                <x-heroicon-o-home />
                <span class="panel-nav-item-text">Dashboard</span>
            </a>
        </div>

        <div class="panel-nav-group">
            <span class="panel-nav-label">Campioni</span>

            <a href="{{ route('samples.index') }}"
               class="panel-nav-item {{ request()->routeIs('samples.index') ? 'active' : '' }}" title="Lista campioni">
                <x-heroicon-o-beaker />
                <span class="panel-nav-item-text">Lista campioni</span>
                <span class="panel-nav-count">{{ $sidebarSampleCount }}</span>
            </a>

            <a href="{{ route('samples.create') }}"
               class="panel-nav-item {{ request()->routeIs('samples.create') ? 'active' : '' }}" title="Nuovo campione">
                <x-heroicon-o-plus-circle />
                <span class="panel-nav-item-text">Nuovo campione</span>
            </a>
        </div>

        <div class="panel-nav-group">
            <span class="panel-nav-label">Clienti</span>

            <a href="{{ route('clients.index') }}"
               class="panel-nav-item {{ request()->routeIs('clients.index') ? 'active' : '' }}" title="Lista clienti">
                <x-heroicon-o-users />
                <span class="panel-nav-item-text">Lista clienti</span>
                <span class="panel-nav-count">{{ $sidebarClientCount }}</span>
            </a>

            <a href="{{ route('clients.create') }}"
               class="panel-nav-item {{ request()->routeIs('clients.create') ? 'active' : '' }}" title="Nuovo cliente">
                <x-heroicon-o-user-plus />
                <span class="panel-nav-item-text">Nuovo cliente</span>
            </a>
        </div>

        @if(auth()->user()->isAdmin())
        <div class="panel-nav-group">
            <span class="panel-nav-label">Archivio</span>

            <a href="{{ route('samples.archived') }}"
               class="panel-nav-item {{ request()->routeIs('samples.archived') ? 'active' : '' }}" title="Campioni archiviati">
                <x-heroicon-o-archive-box />
                <span class="panel-nav-item-text">Campioni archiviati</span>
            </a>

            <a href="{{ route('clients.archived') }}"
               class="panel-nav-item {{ request()->routeIs('clients.archived') ? 'active' : '' }}" title="Clienti archiviati">
                <x-heroicon-o-user-minus />
                <span class="panel-nav-item-text">Clienti archiviati</span>
            </a>
        </div>
        @endif

        @if(auth()->user()->isAdmin())
        <div class="panel-nav-group">
            <span class="panel-nav-label">Amministrazione</span>

            <a href="{{ route('staff.index') }}"
               class="panel-nav-item {{ request()->routeIs('staff.index') ? 'active' : '' }}" title="Lista staff">
                <x-heroicon-o-shield-check />
                <span class="panel-nav-item-text">Lista staff</span>
                <span class="panel-nav-count">{{ $sidebarStaffCount }}</span>
            </a>

            <a href="{{ route('staff.create') }}"
               class="panel-nav-item {{ request()->routeIs('staff.create') ? 'active' : '' }}" title="Nuovo utente">
                <x-heroicon-o-user-plus />
                <span class="panel-nav-item-text">Nuovo utente</span>
            </a>

            <a href="{{ route('sample-types.index') }}"
               class="panel-nav-item {{ request()->routeIs('sample-types.*') ? 'active' : '' }}" title="Tipi di Campione (Matrici)">
                <x-heroicon-o-funnel />
                <span class="panel-nav-item-text">Tipi di Campione</span>
            </a>
        </div>
        @endif

    </nav>

</div>