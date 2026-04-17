<header class="topbar">

    {{-- Bottone espandi panel --}}
    <button class="topbar-expand" id="topbar-expand" onclick="togglePanel()" title="Apri pannello">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M5 4l4 4-4 4" stroke="currentColor"
                  stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    {{-- Breadcrumb --}}
    <div class="topbar-breadcrumb">
        @yield('breadcrumb')
    </div>

    {{-- Ricerca e azioni --}}
    @yield('topbar-search')
    @yield('topbar-actions')

    {{-- Utente --}}
    <div class="topbar-user">
        <a href="{{ route('profile.edit') }}" class="topbar-user-profile" title="Impostazioni Profilo">
            <div class="topbar-user-avatar">
                <x-heroicon-s-user />
            </div>
            <span class="topbar-user-name">{{ Auth::user()->name }}</span>
        </a>
        <div class="topbar-user-separator"></div>
        <form method="POST" action="{{ route('logout') }}" class="topbar-logout-form">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm topbar-logout-btn" title="Esci dal sistema">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
            </button>
        </form>
    </div>

</header>