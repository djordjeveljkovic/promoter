<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Global Alpine cloak: hides elements with [x-cloak] until Alpine initialises,
     so modals/dropdowns never flash visible during page load or wire:navigate
     morphing. Defined globally so every page has it, not just pages that
     happen to render the Livewire component containing its own <style>. --}}
<style>[x-cloak] { display: none !important; }</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
</content>
</invoke>