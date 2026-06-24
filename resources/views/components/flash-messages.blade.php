@once
    @php
        $toastSlots = [
            'success' => ['variant' => 'success', 'timeout' => 5000],
            'error'   => ['variant' => 'danger',  'timeout' => 7000],
            'info'    => ['variant' => 'info',    'timeout' => 5000],
        ];
    @endphp

    @if (session('success') || session('error') || session('info') || $errors->any())
        <div class="pointer-events-none fixed inset-x-0 top-4 z-[100] mx-auto flex w-full max-w-sm flex-col gap-3 px-4 sm:right-4 sm:left-auto sm:mx-0 sm:px-0">
            @foreach ($toastSlots as $key => $cfg)
                @if (session($key))
                    <div
                        wire:key="flash-{{ $key }}"
                        x-data="{ open: true }"
                        x-init="
                            const ms = {{ (int) $cfg['timeout'] }};
                            setTimeout(() => { open = false }, ms);
                        "
                        @class(['pointer-events-auto' => true])
                    >
                        <x-ui.alert :variant="$cfg['variant']" position="toast" :dismissable="true">
                            {!! session($key) !!}
                        </x-ui.alert>
                    </div>
                @endif
            @endforeach

            @if ($errors->any() && empty($_GET['test']))
                <div
                    wire:key="flash-validation"
                    x-data="{ open: true }"
                    x-init="setTimeout(() => { open = false }, 10000)"
                    class="pointer-events-auto"
                >
                    <x-ui.alert variant="danger" position="toast" :dismissable="true" :title="__('Please correct the following issues:')">
                        <ul class="list-disc list-inside space-y-0.5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                </div>
            @endif
        </div>
    @endif

    {{-- Re-initialize Alpine on freshly morphed Livewire elements so new flash
         toasts get the auto-dismiss timeout. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.hook('morph.updated', () => {
                    // Alpine walks the DOM automatically — nothing to do here.
                });
            }
        });
    </script>
@endonce
</content>
</invoke>