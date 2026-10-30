# Modul Kalender

Salinan struktur modul Majalah untuk pemesanan kalender. Data disimpan di tabel sendiri
(`pesanan_kalender*`), terpisah dari majalah.

Halaman: KORWIL (`pesanan-kalender`), PINWIL (`pesanan-kalender-kotamadya`), JABODETABEK PUW1 (`pesanan-kalender-puw1`).

Pasang:
1. Daftarkan `App\Modules\Kalender\Providers\KalenderServiceProvider::class` di `bootstrap/providers.php`.
2. `composer dump-autoload && php artisan migrate && php artisan optimize:clear`
