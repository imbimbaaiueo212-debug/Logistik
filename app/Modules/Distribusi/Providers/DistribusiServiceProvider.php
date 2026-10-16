<?php

namespace App\Modules\Distribusi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DistribusiServiceProvider extends ServiceProvider
{
    private function path(string $sub = ''): string
    {
        return dirname(__DIR__) . ($sub ? DIRECTORY_SEPARATOR . $sub : '');
    }

    public function boot(): void
    {
        // View dipanggil dengan namespace modul: view('distribusi::<folder>.<nama>')
        if (is_dir($this->path('Resources/views'))) {
            $this->loadViewsFrom($this->path('Resources/views'), 'distribusi');
        }

        // Migration modul ikut terbaca oleh `php artisan migrate`
        if (is_dir($this->path('Database/Migrations'))) {
            $this->loadMigrationsFrom($this->path('Database/Migrations'));
        }

        // Route modul (grup middleware "web", sama seperti routes/web.php)
        if (is_file($this->path('Routes/web.php')) && ! $this->app->routesAreCached()) {
            Route::middleware('web')->group($this->path('Routes/web.php'));
        }
    }
}
