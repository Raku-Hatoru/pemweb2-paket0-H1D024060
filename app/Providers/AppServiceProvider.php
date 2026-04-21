<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('access-admin-area', function (User $user): Response {
            return $user->isAdmin()
                ? Response::allow()
                : Response::deny('Halaman ini hanya bisa diakses admin.');
        });

        Gate::define('access-anggota-area', function (User $user): Response {
            return $user->isAnggota()
                ? Response::allow()
                : Response::deny('Halaman ini hanya bisa diakses anggota.');
        });
    }
}
