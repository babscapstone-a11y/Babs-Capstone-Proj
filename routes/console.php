<?php

use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:cancel-unclaimed', function () {
    $count = Order::cancelUnclaimedPickups();
    $this->info("Cancelled {$count} unclaimed pick-up " . ($count === 1 ? 'order' : 'orders') . '.');
})->purpose('Cancel prepared online orders not picked up by closing time on their pick-up day');

Artisan::command('login:unlock {login : The email or username of the locked account}', function (string $login) {
    $wasLocked = \App\Support\LoginLockout::isLocked($login);
    \App\Support\LoginLockout::clear($login);
    $this->info($wasLocked ? "Unlocked {$login}." : "{$login} wasn't locked; its failed-attempt count was reset.");
})->purpose('Lift a login lockout (3 failed attempts) before the hour is up');

// Needs the Laravel scheduler running: `php artisan schedule:work` locally,
// or a cron entry for `php artisan schedule:run` every minute on the server.
Schedule::command('orders:cancel-unclaimed')->everyFiveMinutes();
