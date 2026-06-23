<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    // Register /broadcasting/auth and load channel authorization callbacks
    // from routes/channels.php so private WebSocket channels (Reverb) work.
    ->withBroadcasting(__DIR__ . '/../routes/channels.php')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Send already-authenticated visitors of guest-only pages (login,
        // register, password reset) to the correct dashboard for their
        // role. Without this they would land on the framework default
        // `/home` route which 404s in this app.
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            $role = $user?->role;
            return match ($role) {
                'admin', 'supreme' => route('dashboard'),
                'promoter_manager' => route('promoter_manager.dashboard'),
                'sub_promoter'     => route('sub_promoter.dashboard'),
                'promoter'         => route('promoter.dashboard'),
                default            => route('promoter.dashboard'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
