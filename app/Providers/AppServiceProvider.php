<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;   // ← Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Set locale Indonesia untuk Carbon (supaya bulan jadi Juni, Juli, dll)
        Carbon::setLocale('id');

        // Helper format harga
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