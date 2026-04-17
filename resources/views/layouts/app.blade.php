<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AER Consulting') }} — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell">
        @include('components.sidebar-panel')
        <div class="app-main">
            @include('components.topbar')
            <div class="app-content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        function togglePanel() {
            const panel = document.getElementById('sidebar-panel');
            const expandBtn = document.getElementById('topbar-expand');
            const isCollapsed = panel.classList.toggle('collapsed');
            expandBtn.classList.toggle('visible', isCollapsed);
            localStorage.setItem('panel_collapsed', isCollapsed ? '1' : '0');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const collapsed = localStorage.getItem('panel_collapsed') === '1';
            if (collapsed) {
                document.getElementById('sidebar-panel').classList.add('collapsed');
                document.getElementById('topbar-expand').classList.add('visible');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Configurazione base SweetAlert per i Toast di notifica
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        // Gestore universale per i pulsanti di eliminazione e completamento
        function confirmAction(event, message) {
            event.preventDefault();
            const form = event.currentTarget.closest('form');
            Swal.fire({
                title: "Conferma operazione",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#078586", // Tema AER Consulting
                cancelButtonColor: "#6B7280",
                confirmButtonText: "Sì, procedi",
                cancelButtonText: "Annulla",
                customClass: {
                    confirmButton: 'rounded-md text-sm font-medium focus:ring-2 focus:ring-offset-2',
                    cancelButton: 'rounded-md text-sm font-medium focus:ring-2 focus:ring-offset-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

    @stack('scripts')

</body>

</html>