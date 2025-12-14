<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

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
        // Global currency Blade directive: @currency(amount)
        Blade::directive('currency', function ($expression) {
            return "<?php echo '৳ ' . number_format($expression, 2); ?>";
        });
    }
}
