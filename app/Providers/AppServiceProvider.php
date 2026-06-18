<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Helper format harga (menggunakan closure agar aman saat config:cache)
        if (!function_exists('format_price')) {
            function format_price($value)
            {
                if (is_null($value) || $value === '' || $value === 0) {
                    return '';
                }

                $num = (float) $value;

                if (floor($num) === $num) {
                    return number_format($num, 0, ',', '.');
                }

                return number_format($num, 2, ',', '.');
            }
        }

        Blade::directive('formatPrice', function ($expression) {
            return "<?php echo format_price({$expression}); ?>";
        });
    }
}