# Modul aplikasi

Setiap folder di `app/Modules/<Nama>` berdiri sendiri:

```
<Nama>/
├── Http/Controllers/     namespace App\Modules\<Nama>\Http\Controllers
├── Models/               namespace App\Modules\<Nama>\Models
├── Database/Migrations/  dibaca otomatis oleh `php artisan migrate`
├── Resources/views/      dipanggil dengan namespace: view('<nama>::folder.view')
├── Routes/web.php        dimuat dengan middleware "web"
├── Providers/<Nama>ServiceProvider.php
└── (opsional) Services/, Exports/, Imports/, Enums/
```

Modul: Master, Import, Order, Majalah, Picking, Pengadaan, Stok, Distribusi, Qc, Packing, OrderManual, Keuangan.
Tetap di inti (bukan modul): login/registrasi, Home, dashboard, layouts, partials, components, model User dan Module.

## Modul baru
1. Buat folder dengan struktur di atas, salin salah satu provider dan ganti nama/slug view.
2. Daftarkan provider di `bootstrap/providers.php`.
3. Jalankan `composer dump-autoload` dan `php artisan optimize:clear`.

## Aturan
- Nama rute (`route('po.index')`) tidak memakai prefix modul, jadi harus unik antar modul.
- Modul boleh memakai model modul lain lewat `use App\Modules\<Lain>\Models\X;`, tapi usahakan searah:
  Master (produk, supplier, gudang) dipakai oleh banyak modul, bukan sebaliknya.
