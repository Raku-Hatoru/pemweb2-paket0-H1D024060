<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BorrowingHistoryController extends Controller
{
    /**
     * Display the authenticated member's borrowing history.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $member = $user->loadMissing('member')->member;

        abort_if(! $member, 404);

        $baseQuery = Borrowing::query()->whereBelongsTo($member);
        $historyQuery = (clone $baseQuery)
            ->select([
                'id',
                'member_id',
                'borrow_date',
                'due_date',
                'return_date',
                'status',
                'total_fine',
                'notes',
            ])
            ->with([
                'borrowingItems:id,borrowing_id,book_id,qty',
                'borrowingItems.book:id,title,category_id',
                'borrowingItems.book.category:id,name',
            ])
            ->withSum('borrowingItems as total_books', 'qty')
            ->latest('borrow_date');

        return view('anggota.borrowings.history', [
            'member' => $member,
            'summary' => [
                'total_transactions' => (clone $baseQuery)->count(),
                'active_transactions' => (clone $baseQuery)->whereNull('return_date')->count(),
                'returned_transactions' => (clone $baseQuery)->whereNotNull('return_date')->count(),
                'total_fine' => (int) (clone $baseQuery)->sum('total_fine'),
            ],
            'borrowings' => $historyQuery->paginate(10),
        ]);
    }
}
