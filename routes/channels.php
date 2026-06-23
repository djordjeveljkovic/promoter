<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given callback returns true if the given user
| may listen to the channel. See the Laravel docs for details.
|
*/

// Private per-user channel (already used by Laravel's built-in broadcasting
// notifications, e.g. when a user receives a notification).
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Admin/Sales-ops real-time channel
|--------------------------------------------------------------------------
|
| Authorizes listeners on the private `orders` channel. Only admins and
| supreme admins can subscribe — this is what prevents regular promoters
| from seeing other people's order status changes.
|
| Note: we intentionally do NOT filter per-order here. The admin orders
| index page filters client-side by `order_id` from the payload, which
| keeps the channel simple and lets us add extra event types (e.g. new
| order placed) on the same channel without extra subscriptions.
*/
Broadcast::channel('orders', function (User $user) {
    return $user->isAdmin();
});