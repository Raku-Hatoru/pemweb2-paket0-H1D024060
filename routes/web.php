<?php

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

Route::get('/dashboard', function (Request $request): RedirectResponse {
    /** @var User $user */
    $user = $request->user();

    return redirect(route($user->dashboardRouteName(), absolute: false));
})->middleware('auth')->name('dashboard');

Route::view('/admin/dashboard', 'welcome')
    ->middleware(['auth', 'can:access-admin-area'])
    ->name('admin.dashboard');

Route::view('/anggota/dashboard', 'welcome')
    ->middleware(['auth', 'can:access-anggota-area'])
    ->name('anggota.dashboard');
