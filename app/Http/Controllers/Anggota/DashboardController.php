<?php

namespace App\Http\Controllers\Anggota;

use App\BorrowingStatus;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the member dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $member = $user->loadMissing('member')->member;

        $memberStats = $member instanceof Member
            ? [
                [
                    'label' => 'Sedang dipinjam',
                    'value' => Borrowing::query()
                        ->whereBelongsTo($member)
                        ->whereIn('status', [BorrowingStatus::Dipinjam, BorrowingStatus::Terlambat])
                        ->count(),
                    'description' => 'Transaksi aktif yang masih harus dikembalikan.',
                ],
                [
                    'label' => 'Riwayat peminjaman',
                    'value' => Borrowing::query()
                        ->whereBelongsTo($member)
                        ->count(),
                    'description' => 'Seluruh transaksi yang pernah tercatat untuk akun ini.',
                ],
                [
                    'label' => 'Total denda',
                    'value' => 'Rp '.number_format(
                        Borrowing::query()->whereBelongsTo($member)->sum('total_fine'),
                        thousands_separator: '.'
                    ),
                    'description' => 'Akumulasi denda berdasarkan histori peminjaman.',
                ],
            ]
            : [];

        $borrowings = $member instanceof Member
            ? Borrowing::query()
                ->select(['id', 'member_id', 'borrow_date', 'due_date', 'return_date', 'status', 'total_fine'])
                ->whereBelongsTo($member)
                ->with([
                    'borrowingItems:id,borrowing_id,book_id,qty',
                    'borrowingItems.book:id,title,category_id',
                    'borrowingItems.book.category:id,name',
                ])
                ->latest('borrow_date')
                ->limit(5)
                ->get()
            : collect();

        return view('anggota.dashboard', [
            'member' => $member,
            'memberStats' => $memberStats,
            'borrowings' => $borrowings,
            'activeBooks' => $member instanceof Member
                ? (int) $member->borrowingItems()
                    ->whereHas('borrowing', fn ($query) => $query->active())
                    ->sum('qty')
                : 0,
        ]);
    }
}
