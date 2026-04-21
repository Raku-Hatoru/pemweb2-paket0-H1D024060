<?php

namespace App\Http\Controllers\Admin;

use App\BorrowingStatus;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the administrator dashboard.
     */
    public function index(): View
    {
        $stats = [
            [
                'label' => 'Total kategori',
                'value' => Category::query()->count(),
                'description' => 'Fondasi klasifikasi buku dan filter laporan.',
            ],
            [
                'label' => 'Judul buku',
                'value' => Book::query()->count(),
                'description' => 'Jumlah data buku yang siap dikelola admin.',
            ],
            [
                'label' => 'Anggota aktif',
                'value' => Member::query()->count(),
                'description' => 'Profil anggota yang sudah tersambung ke akun login.',
            ],
            [
                'label' => 'Peminjaman berjalan',
                'value' => Borrowing::query()
                    ->whereIn('status', [BorrowingStatus::Dipinjam, BorrowingStatus::Terlambat])
                    ->count(),
                'description' => 'Transaksi yang masih perlu dipantau sampai pengembalian.',
            ],
        ];

        $recentBorrowings = Borrowing::query()
            ->select(['id', 'member_id', 'borrow_date', 'due_date', 'status'])
            ->with([
                'member:id,user_id,member_code',
                'member.user:id,name',
            ])
            ->withSum('borrowingItems as total_books', 'qty')
            ->latest('borrow_date')
            ->limit(5)
            ->get();

        $latestCategories = Category::query()
            ->select(['id', 'name', 'slug', 'updated_at'])
            ->withCount('books')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentMembers = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->where('role', UserRole::Anggota->value)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentBorrowings' => $recentBorrowings,
            'latestCategories' => $latestCategories,
            'recentMembers' => $recentMembers,
        ]);
    }
}
