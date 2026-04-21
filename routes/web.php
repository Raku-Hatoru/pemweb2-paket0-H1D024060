<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Anggota\DashboardController as AnggotaDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function (Request $request): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        return redirect(route($user->dashboardRouteName(), absolute: false));
    })->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('can:access-admin-area')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::resource('categories', CategoryController::class)->except('show');
        });

    Route::prefix('anggota')
        ->name('anggota.')
        ->middleware('can:access-anggota-area')
        ->group(function () {
            Route::get('/dashboard', [AnggotaDashboardController::class, 'index'])->name('dashboard');
        });
});
