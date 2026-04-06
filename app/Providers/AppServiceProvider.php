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
        // Helper untuk format harga bersih (hilangkan ,00 jika angka bulat)
        if (!function_exists('format_price')) {
            function format_price($value)
            {
                if (is_null($value) || $value === '' || $value === 0) {
                    return '';
                }

                $num = (float) $value;

                // Jika angka bulat (tidak ada desimal), tampilkan tanpa koma desimal
                if (floor($num) === $num) {
                    return number_format($num, 0, ',', '.');
                }

                // Jika ada desimal, tampilkan 2 angka di belakang koma
                return number_format($num, 2, ',', '.');
            }
        }

        // Optional: Daftarkan sebagai Blade directive jika ingin pakai @formatPrice
        Blade::directive('formatPrice', function ($expression) {
            return "<?php echo format_price({$expression}); ?>";
        });
    }
}