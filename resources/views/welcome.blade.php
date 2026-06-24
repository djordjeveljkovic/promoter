<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">

                @auth
                    @php
                        // Send each logged-in user to the dashboard they can
                        // actually see, instead of always forcing the admin
                        // one (which 403s for non-admin roles).
                        $dashboardRoute = match (auth()->user()->role) {
                            'sub_promoter'     => 'sub_promoter.dashboard',
                            'promoter_manager' => 'promoter_manager.dashboard',
                            'promoter'         => 'promoter.dashboard',
                            default            => 'dashboard', // admin, superadmin, supreme
                        };
                    @endphp
                    {{-- Redirect immediately if logged in --}}
                    <script>window.location.href = '{{ route($dashboardRoute) }}';</script>
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
</content>
</invoke>