<?php

namespace App\Modules\PengeluaranLogistik\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PengeluaranLogistikServiceProvider extends ServiceProvider
{
    private function path(string $sub = ''): string
    {
        return dirname(__DIR__) . ($sub ? DIRECTORY_SEPARATOR . $sub : '');
    }

    public function boot(): void
    {
        // View: view('pengeluaran-logistik::<nama>')
        if (is_dir($this->path('Resources/views'))) {
            $this->loadViewsFrom($this->path('Resources/views'), 'pengeluaran-logistik');
        }

        if (is_dir($this->path('Database/Migrations'))) {
            $this->loadMigrationsFrom($this->path('Database/Migrations'));
        }

        if (is_file($this->path('Routes/web.php')) && ! $this->app->routesAreCached()) {
            Route::middleware('web')->group($this->path('Routes/web.php'));
        }
    }
}
