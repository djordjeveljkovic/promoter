{{-- Logo icon used by x-app-logo (sidebar/header) and the auth card.
     Tries a small chain of asset paths so the icon never 404s regardless
     of which files were uploaded to the public/ directory:
       1. /img/logo.png       — the original (64x64 PNG) that lives in public/img/
       2. /logo.png           — the 32x32 icon that is tracked in git
       3. /logo.svg           — the SVG version that is tracked in git
     The first one that exists on disk wins; the rest are ignored. --}}
@php
    $logoCandidates = [
        'img/logo.png',
        'logo.png',
        'logo.svg',
        'logo_white.webp',
    ];
    $logoSrc = null;
    foreach ($logoCandidates as $candidate) {
        if (file_exists(public_path($candidate))) {
            $logoSrc = asset($candidate);
            break;
        }
    }
    $logoSrc = $logoSrc ?? asset('logo.svg');
@endphp
<img src="{{ $logoSrc }}" {{ $attributes }}/>
