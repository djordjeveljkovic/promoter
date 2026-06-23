@props([
    'action' => null,
    'method' => 'GET',
    'autosubmit' => false,   // true => submit on change of select
])

<form method="{{ $method }}"
      action="{{ $action }}"
      {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end']) }}>
    @csrf

    {{ $slot }}

    @if($autosubmit)
        <script>
            // Auto-submit parent <form> on change of any select inside.
            // Loaded once per form because each component re-renders.
            (function () {
                if (window.__uiFilterFormAutosubmitBound) return;
                window.__uiFilterFormAutosubmitBound = true;
                document.addEventListener('change', function (e) {
                    const sel = e.target.closest('form select[autosubmit]');
                    if (!sel) return;
                    sel.form?.submit();
                });
            })();
        </script>
    @endif
</form>
