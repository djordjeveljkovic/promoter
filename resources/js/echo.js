// Laravel Echo setup — self-hosted WebSocket via laravel/reverb.
//
// NOTE: Despite the import name, `pusher-js` is just the free, open-source
// (MIT) client that speaks the Pusher wire protocol. It does NOT contact
// pusher.com and does NOT require any paid license. We point it at our
// own Reverb server (host from VITE_REVERB_HOST) which speaks the same
// protocol. Everything here is free.
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
