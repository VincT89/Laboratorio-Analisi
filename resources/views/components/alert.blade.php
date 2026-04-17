{{-- ============================================================
     Alert — messaggi flash
     $type: success | error | warning | info
     $message: testo del messaggio
     ============================================================ --}}

<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>