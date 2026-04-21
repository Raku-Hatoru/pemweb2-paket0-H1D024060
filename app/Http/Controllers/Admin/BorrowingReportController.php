<?php

namespace App\Http\Controllers\Admin;

use App\BorrowingReportPdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowingReportRequest;
use App\Models\Borrowing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BorrowingReportController extends Controller
{
    public function __construct(
        private readonly BorrowingReportPdf $borrowingReportPdf
    ) {}

    /**
     * Display the borrowing report page.
     */
    public function index(BorrowingReportRequest $request): View
    {
        $reportQuery = $this->reportQuery($request);
        $summary = $this->buildSummary((clone $reportQuery)->get());
        $borrowings = $reportQuery
            ->paginate(12)
            ->withQueryString();

        return view('admin.reports.borrowings.index', [
            'borrowings' => $borrowings,
            'summary' => $summary,
            'periodLabel' => $request->periodLabel(),
        ]);
    }

    /**
     * Download the borrowing report as a PDF file.
     */
    public function exportPdf(BorrowingReportRequest $request): Response
    {
        $borrowings = $this->reportQuery($request)->get();
        $summary = $this->buildSummary($borrowings);
        $pdf = $this->borrowingReportPdf->build(
            title: 'Laporan Peminjaman Perpustakaan',
            periodLabel: $request->periodLabel(),
            summary: $summary,
            borrowings: $borrowings
        );

        $filename = 'laporan-peminjaman-'.$request->rangeStart()->format('Y-m-d').'-'.$request->rangeEnd()->format('Y-m-d').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    private function reportQuery(BorrowingReportRequest $request): Builder
    {
        return Borrowing::query()
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
                'borrowingItems.book:id,title',
            ])
            ->withSum('borrowingItems as total_books', 'qty')
            ->whereBetween('borrow_date', [
                $request->rangeStart()->toDateString(),
                $request->rangeEnd()->toDateString(),
            ])
            ->latest('borrow_date');
    }

    /**
     * @param  Collection<int, Borrowing>  $borrowings
     * @return array<string, int>
     */
    private function buildSummary(Collection $borrowings): array
    {
        return [
            'total_transactions' => $borrowings->count(),
            'total_books' => (int) $borrowings->sum(fn (Borrowing $borrowing): int => (int) $borrowing->total_books),
            'returned_transactions' => $borrowings->filter(fn (Borrowing $borrowing): bool => $borrowing->return_date !== null)->count(),
            'active_transactions' => $borrowings->filter(fn (Borrowing $borrowing): bool => $borrowing->return_date === null)->count(),
            'total_fine' => (int) $borrowings->sum('total_fine'),
        ];
    }
}
