<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination::compact');

        // Strong password everywhere a password is set (staff accounts, customer registration,
        // change password, and both reset-password flows): 8+ characters with lowercase,
        // uppercase, a number and a special character.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());

        // @unit($item->unit) prints the short unit name, e.g. "Kilogram" → "Kg" (App\Support\Units)
        Blade::directive('unit', fn ($expression) => "<?php echo e(\\App\\Support\\Units::short({$expression})); ?>");
    }
}
