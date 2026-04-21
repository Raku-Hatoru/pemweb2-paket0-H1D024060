<?php

namespace App\Http\Controllers\Admin;

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
        $borrowedBooksCount = (int) Borrowing::query()
            ->whereNull('return_date')
            ->join('borrowing_items', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->sum('borrowing_items.qty');

        $totalBooks = (int) Book::query()->sum('stock') + $borrowedBooksCount;
        $totalMembers = (int) Member::query()->count();
        $currentMonthFine = (int) Borrowing::query()
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('total_fine');

        $stats = [
            [
                'label' => 'Total buku',
                'value' => number_format($totalBooks, thousands_separator: '.'),
                'description' => 'Total eksemplar koleksi perpustakaan, termasuk yang sedang dipinjam.',
            ],
            [
                'label' => 'Buku sedang dipinjam',
                'value' => number_format($borrowedBooksCount, thousands_separator: '.'),
                'description' => 'Total eksemplar yang sedang keluar dan belum kembali.',
            ],
            [
                'label' => 'Total anggota',
                'value' => number_format($totalMembers, thousands_separator: '.'),
                'description' => 'Anggota perpustakaan yang sudah punya akun dan profil lengkap.',
            ],
            [
                'label' => 'Denda bulan ini',
                'value' => 'Rp '.number_format($currentMonthFine, thousands_separator: '.'),
                'description' => 'Akumulasi denda dari transaksi yang selesai pada bulan berjalan.',
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

        $membersNeedingAttention = Member::query()
            ->select(['id', 'user_id', 'member_code'])
            ->whereHas('borrowings', fn ($query) => $query->active())
            ->with(['user:id,name'])
            ->withCount([
                'borrowings as active_borrowings_count' => fn ($query) => $query->active(),
            ])
            ->ordered()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentBorrowings' => $recentBorrowings,
            'latestCategories' => $latestCategories,
            'recentMembers' => $recentMembers,
            'membersNeedingAttention' => $membersNeedingAttention,
        ]);
    }
}
