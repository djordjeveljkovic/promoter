import Alpine from 'alpinejs';
import ticketOrder from './ticket_items.js'
// Side-effect import: registers Laravel Echo on window.Echo so the admin
// orders page (and any other page that wants real-time updates) can
// subscribe to private channels via Echo.private('orders').
import './echo.js';

Alpine.data('ticketOrder', ticketOrder);
Alpine.start();
