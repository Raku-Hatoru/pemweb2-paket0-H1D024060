<?php

namespace Tests\Feature\Admin;

use App\BorrowingStatus;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_borrowing_report_routes(): void
    {
        $this->get(route('admin.reports.borrowings'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.reports.borrowings.pdf'))
            ->assertRedirect(route('login'));
    }

    public function test_anggota_cannot_access_borrowing_reports(): void
    {
        $anggota = User::factory()->create();

        $this->actingAs($anggota)
            ->get(route('admin.reports.borrowings'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_borrowing_report_by_month(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 8,
        ]);
        $aprilMember = Member::factory()->for(User::factory()->state([
            'name' => 'April Reader',
        ]))->create();
        $mayMember = Member::factory()->for(User::factory()->state([
            'name' => 'May Reader',
        ]))->create();

        $this->createBorrowingWithBook($aprilMember, $book, [
            'borrow_date' => '2026-04-03',
            'due_date' => '2026-04-10',
            'return_date' => '2026-04-12',
            'status' => BorrowingStatus::Dikembalikan,
            'total_fine' => 2000,
            'notes' => 'Transaksi April',
        ]);

        $this->createBorrowingWithBook($mayMember, $book, [
            'borrow_date' => '2026-05-03',
            'due_date' => '2026-05-10',
            'return_date' => null,
            'status' => BorrowingStatus::Dipinjam,
            'total_fine' => 0,
            'notes' => 'Transaksi Mei',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.borrowings', [
            'month' => '2026-04',
        ]));

        $response->assertOk()
            ->assertSee('April Reader')
            ->assertSee('Transaksi April')
            ->assertSee('Rp 2.000')
            ->assertDontSee('May Reader')
            ->assertDontSee('Transaksi Mei');
    }

    public function test_admin_can_filter_borrowing_report_by_custom_date_range(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 8,
        ]);
        $member = Member::factory()->create();

        $this->createBorrowingWithBook($member, $book, [
            'borrow_date' => '2026-04-05',
            'due_date' => '2026-04-12',
            'notes' => 'Di luar rentang',
        ]);

        $this->createBorrowingWithBook($member, $book, [
            'borrow_date' => '2026-04-20',
            'due_date' => '2026-04-27',
            'notes' => 'Masuk rentang',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.borrowings', [
            'date_from' => '2026-04-10',
            'date_to' => '2026-04-30',
        ]));

        $response->assertOk()
            ->assertSee('Masuk rentang')
            ->assertDontSee('Di luar rentang');
    }

    public function test_admin_can_export_borrowing_report_to_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 8,
        ]);
        $member = Member::factory()->for(User::factory()->state([
            'name' => 'PDF Reader',
        ]))->create();

        $this->createBorrowingWithBook($member, $book, [
            'borrow_date' => '2026-04-08',
            'due_date' => '2026-04-15',
            'return_date' => '2026-04-16',
            'status' => BorrowingStatus::Dikembalikan,
            'total_fine' => 1000,
            'notes' => 'Transaksi PDF',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.borrowings.pdf', [
            'month' => '2026-04',
        ]));

        $response->assertDownload('laporan-peminjaman-2026-04-01-2026-04-30.pdf');
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertStringContainsString('%PDF-1.4', $response->getContent());
    }

    private function createBorrowingWithBook(
        Member $member,
        Book $book,
        array $borrowingAttributes = [],
        int $qty = 1
    ): Borrowing {
        $borrowing = Borrowing::factory()->for($member)->create($borrowingAttributes);

        BorrowingItem::factory()->for($borrowing)->for($book)->create([
            'qty' => $qty,
        ]);

        return $borrowing;
    }
}
