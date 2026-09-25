<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\DistributionOrder;
use App\Models\DistributionPasif;
use App\Models\ManualDistributionOrder;

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

        // Daftar nama ekspedisi unik (JNE, TIKI, Lion Parcel, dst) untuk submenu "Service" di sidebar
        View::composer('partials.home-sidebar', function ($view) {
            $ekspedisiOptions = collect();

            if (Auth::check()) {
                try {
                    // Kolom 'ekspedisi' ada langsung di tabel distribution_orders
                    $dariAktif = DistributionOrder::query()
                        ->whereNotNull('ekspedisi')->where('ekspedisi', '!=', '')
                        ->distinct()->pluck('ekspedisi');

                    $dariPasif = DistributionPasif::query()
                        ->whereNotNull('ekspedisi')->where('ekspedisi', '!=', '')
                        ->distinct()->pluck('ekspedisi');

                    $dariManual = ManualDistributionOrder::query()
                        ->whereNotNull('ekspedisi')->where('ekspedisi', '!=', '')
                        ->distinct()->pluck('ekspedisi');

                    $ekspedisiOptions = $dariAktif
                        ->merge($dariPasif)
                        ->merge($dariManual)
                        ->map(fn ($e) => is_string($e) ? trim($e) : $e)
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                } catch (\Throwable $e) {
                    // Kalau ada error (mis. nama kolom beda di salah satu tabel), sidebar tetap tampil normal
                    // tanpa submenu Service, dan errornya tercatat di storage/logs/laravel.log
                    Log::warning('Gagal mengambil daftar ekspedisi untuk sidebar: ' . $e->getMessage());
                    $ekspedisiOptions = collect();
                }
            }

            $view->with('ekspedisiOptions', $ekspedisiOptions);
        });
    }
}