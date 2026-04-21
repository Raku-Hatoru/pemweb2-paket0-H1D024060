<?php

use App\Http\Controllers\Admin\BorrowingController;
use App\Http\Controllers\Admin\BorrowingReportController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Anggota\BorrowingHistoryController;
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
            Route::resource('members', MemberController::class)->except('show');
            Route::resource('borrowings', BorrowingController::class)->only(['index', 'create', 'store']);
            Route::get('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnForm'])->name('borrowings.return');
            Route::patch('/borrowings/{borrowing}/return', [BorrowingController::class, 'storeReturn'])->name('borrowings.return.store');
            Route::get('/reports/borrowings', [BorrowingReportController::class, 'index'])->name('reports.borrowings');
            Route::get('/reports/borrowings/pdf', [BorrowingReportController::class, 'exportPdf'])->name('reports.borrowings.pdf');
        });

    Route::prefix('anggota')
        ->name('anggota.')
        ->middleware('can:access-anggota-area')
        ->group(function () {
            Route::get('/dashboard', [AnggotaDashboardController::class, 'index'])->name('dashboard');
            Route::get('/borrowings/history', [BorrowingHistoryController::class, 'index'])->name('borrowings.history');
        });
});
