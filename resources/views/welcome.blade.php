<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">

                @auth
                    {{-- Redirect immediately if logged in --}}
                    <script>window.location.href = '{{ route('dashboard') }}';</script>
                @else
                    {{-- Guest: Show login --}}
                    <a href="{{ route('login') }}" class="flex flex-col w-full text-2xl text-white items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                        <img src="{{ asset('logo_white.webp') }}" />
                    </a>
                @endauth

            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

    </body>
</html>

