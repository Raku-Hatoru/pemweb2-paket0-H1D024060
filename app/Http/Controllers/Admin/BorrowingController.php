<?php

namespace App\Http\Controllers\Admin;

use App\BorrowingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnBorrowingRequest;
use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the borrowings.
     */
    public function index(): View
    {
        return view('admin.borrowings.index', [
            'borrowings' => Borrowing::query()
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
                    'member:id,user_id,member_code',
                    'member.user:id,name',
                    'borrowingItems:id,borrowing_id,book_id,qty',
                    'borrowingItems.book:id,title,category_id',
                    'borrowingItems.book.category:id,name',
                ])
                ->withSum('borrowingItems as total_books', 'qty')
                ->latest('borrow_date')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new borrowing.
     */
    public function create(): View
    {
        return view('admin.borrowings.create', [
            'members' => Member::query()
                ->select(['id', 'user_id', 'member_code'])
                ->with('user:id,name')
                ->withSum([
                    'borrowingItems as active_books_count' => fn ($query) => $query->whereHas(
                        'borrowing',
                        fn ($borrowingQuery) => $borrowingQuery->active()
                    ),
                ], 'qty')
                ->ordered()
                ->get(),
            'availableBooks' => Book::query()
                ->select(['id', 'category_id', 'title', 'author', 'stock'])
                ->available()
                ->with('category:id,name')
                ->orderBy('title')
                ->get(),
        ]);
    }

    /**
     * Store a newly created borrowing in storage.
     *
     * @throws ValidationException
     */
    public function store(StoreBorrowingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $member = Member::query()
                ->whereKey($validated['member_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $requestedItems = collect($validated['items'])
                ->map(fn (array $item): array => [
                    'book_id' => (int) $item['book_id'],
                    'qty' => (int) $item['qty'],
                ]);

            $requestedBookIds = $requestedItems->pluck('book_id');
            $requestedBookQuantities = $requestedItems->sum('qty');

            $lockedBooks = Book::query()
                ->select(['id', 'title', 'stock'])
                ->whereIn('id', $requestedBookIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $activeBorrowingIds = Borrowing::query()
                ->whereBelongsTo($member)
                ->active()
                ->lockForUpdate()
                ->pluck('id');

            $activeBooksCount = $activeBorrowingIds->isEmpty()
                ? 0
                : DB::table('borrowing_items')
                    ->whereIn('borrowing_id', $activeBorrowingIds)
                    ->sum('qty');

            if ($activeBooksCount + $requestedBookQuantities > 3) {
                throw ValidationException::withMessages([
                    'items' => 'Anggota hanya boleh memiliki maksimal 3 buku aktif dalam waktu bersamaan.',
                ]);
            }

            if ($activeBorrowingIds->isNotEmpty()) {
                $duplicatedBookIds = DB::table('borrowing_items')
                    ->whereIn('borrowing_id', $activeBorrowingIds)
                    ->whereIn('book_id', $requestedBookIds)
                    ->pluck('book_id');

                if ($duplicatedBookIds->isNotEmpty()) {
                    $duplicatedTitles = $lockedBooks
                        ->only($duplicatedBookIds->all())
                        ->pluck('title')
                        ->implode(', ');

                    throw ValidationException::withMessages([
                        'items' => "Anggota masih memiliki pinjaman aktif untuk judul berikut: {$duplicatedTitles}.",
                    ]);
                }
            }

            $requestedItems->each(function (array $item) use ($lockedBooks): void {
                /** @var Book|null $book */
                $book = $lockedBooks->get($item['book_id']);

                if (! $book || $book->stock < $item['qty']) {
                    $bookTitle = $book?->title ?? 'Buku terpilih';

                    throw ValidationException::withMessages([
                        'items' => "{$bookTitle} tidak memiliki stok cukup untuk dipinjam.",
                    ]);
                }

                $book->decrement('stock', $item['qty']);
            });

            $borrowDate = Carbon::parse($validated['borrow_date']);

            $borrowing = Borrowing::create([
                'member_id' => $member->getKey(),
                'borrow_date' => $borrowDate->toDateString(),
                'due_date' => $borrowDate->copy()->addDays(7)->toDateString(),
                'return_date' => null,
                'status' => BorrowingStatus::Dipinjam,
                'total_fine' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $borrowing->borrowingItems()->createMany(
                $requestedItems
                    ->map(fn (array $item): array => [
                        'book_id' => $item['book_id'],
                        'qty' => $item['qty'],
                    ])
                    ->all()
            );
        }, attempts: 5);

        return redirect()
            ->route('admin.borrowings.index')
            ->with('status', 'Transaksi peminjaman berhasil disimpan.');
    }

    /**
     * Show the return form for the specified borrowing.
     */
    public function returnForm(Borrowing $borrowing): View
    {
        abort_if(! $borrowing->canBeReturned(), 404);

        $borrowing->load([
            'member:id,user_id,member_code',
            'member.user:id,name,email',
            'borrowingItems:id,borrowing_id,book_id,qty',
            'borrowingItems.book:id,title,stock',
        ]);

        $defaultReturnDate = now();

        return view('admin.borrowings.return', [
            'borrowing' => $borrowing,
            'defaultReturnDate' => $defaultReturnDate->toDateString(),
            'defaultLateDays' => $borrowing->lateDaysFor($defaultReturnDate),
            'defaultFine' => $borrowing->fineFor($defaultReturnDate),
        ]);
    }

    /**
     * Store the return for the specified borrowing.
     *
     * @throws ValidationException
     */
    public function storeReturn(ReturnBorrowingRequest $request, Borrowing $borrowing): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($borrowing, $validated): void {
            $lockedBorrowing = Borrowing::query()
                ->whereKey($borrowing->getKey())
                ->with(['borrowingItems:id,borrowing_id,book_id,qty'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBorrowing->canBeReturned()) {
                throw ValidationException::withMessages([
                    'return_date' => 'Transaksi ini sudah dikembalikan sebelumnya.',
                ]);
            }

            $returnDate = Carbon::parse($validated['return_date']);
            $lockedBooks = Book::query()
                ->select(['id', 'stock'])
                ->whereIn('id', $lockedBorrowing->borrowingItems->pluck('book_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lockedBorrowing->borrowingItems->each(function ($item) use ($lockedBooks): void {
                /** @var Book|null $book */
                $book = $lockedBooks->get($item->book_id);

                if ($book) {
                    $book->increment('stock', $item->qty);
                }
            });

            $lockedBorrowing->update([
                'return_date' => $returnDate->toDateString(),
                'status' => $lockedBorrowing->resolvedStatusFor($returnDate),
                'total_fine' => $lockedBorrowing->fineFor($returnDate),
            ]);
        }, attempts: 5);

        return redirect()
            ->route('admin.borrowings.index')
            ->with('status', 'Pengembalian buku berhasil disimpan.');
    }
}
