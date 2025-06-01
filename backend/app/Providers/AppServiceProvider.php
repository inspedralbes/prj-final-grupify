<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FormUser;
use App\Observers\FormUserObserver;
use Illuminate\Support\Facades\Schema;

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
        // Registrar el observador solo si existe la tabla form_user
        try {
            if (Schema::hasTable('form_user')) {
                FormUser::observe(FormUserObserver::class);
            }
        } catch (\Exception $e) {
            // Si hay un error (por ejemplo, si la base de datos aún no está configurada)
            // simplemente continuamos sin registrar el observador
        }
    }
}
