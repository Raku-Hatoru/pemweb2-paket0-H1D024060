<?php

namespace Tests\Feature\Admin;

use App\BorrowingStatus;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_borrowing_routes(): void
    {
        $this->get(route('admin.borrowings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_anggota_cannot_access_borrowing_management(): void
    {
        $anggota = User::factory()->create();

        $this->actingAs($anggota)
            ->get(route('admin.borrowings.index'))
            ->assertForbidden();
    }

    public function test_create_form_only_shows_books_with_available_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $availableBook = Book::factory()->for($category)->create([
            'title' => 'Laravel Lanjut',
            'stock' => 2,
        ]);
        $emptyBook = Book::factory()->for($category)->create([
            'title' => 'Buku Habis',
            'stock' => 0,
        ]);
        Member::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.borrowings.create'));

        $response->assertOk()
            ->assertSee($availableBook->title)
            ->assertDontSee($emptyBook->title);
    }

    public function test_admin_can_view_all_borrowing_history_records(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 10,
        ]);
        $memberOne = Member::factory()->for(User::factory()->state([
            'name' => 'Aulia Riwayat',
        ]))->create();
        $memberTwo = Member::factory()->for(User::factory()->state([
            'name' => 'Bima Riwayat',
        ]))->create();

        $borrowingOne = $this->createBorrowingWithBook($memberOne, $book, [
            'notes' => 'Riwayat Aulia',
        ]);
        $borrowingTwo = $this->createBorrowingWithBook($memberTwo, $book, [
            'notes' => 'Riwayat Bima',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.borrowings.index'));

        $response->assertOk()
            ->assertSee($borrowingOne->member->user->name)
            ->assertSee($borrowingTwo->member->user->name)
            ->assertSee('Riwayat Aulia')
            ->assertSee('Riwayat Bima');
    }

    public function test_admin_can_store_multi_book_borrowing_and_reduce_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $member = Member::factory()->create();
        $bookOne = Book::factory()->for($category)->create([
            'title' => 'Laravel Praktis',
            'stock' => 3,
        ]);
        $bookTwo = Book::factory()->for($category)->create([
            'title' => 'Basis Data Lanjut',
            'stock' => 2,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.borrowings.store'), [
            'member_id' => $member->getKey(),
            'borrow_date' => '2026-04-21',
            'notes' => 'Pinjaman awal semester',
            'items' => [
                ['book_id' => $bookOne->getKey(), 'qty' => 1],
                ['book_id' => $bookTwo->getKey(), 'qty' => 2],
            ],
        ]);

        $response->assertRedirect(route('admin.borrowings.index', absolute: false));

        $borrowing = Borrowing::query()->with('borrowingItems')->first();

        $this->assertNotNull($borrowing);
        $this->assertSame(BorrowingStatus::Dipinjam, $borrowing->status);
        $this->assertSame('2026-04-28', $borrowing->due_date->toDateString());
        $this->assertSame(2, $borrowing->borrowingItems->count());
        $this->assertSame(2, $bookOne->fresh()->stock);
        $this->assertSame(0, $bookTwo->fresh()->stock);
    }

    public function test_admin_can_process_return_and_restore_book_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $member = Member::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 1,
        ]);

        $borrowing = Borrowing::factory()->for($member)->create([
            'borrow_date' => '2026-04-01',
            'due_date' => '2026-04-08',
            'return_date' => null,
            'status' => BorrowingStatus::Dipinjam,
            'total_fine' => 0,
        ]);

        BorrowingItem::factory()->for($borrowing)->for($book)->create([
            'qty' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.borrowings.return.store', $borrowing), [
                'return_date' => '2026-04-10',
            ]);

        $response->assertRedirect(route('admin.borrowings.index', absolute: false));

        $borrowing->refresh();

        $this->assertSame(BorrowingStatus::Dikembalikan, $borrowing->status);
        $this->assertSame('2026-04-10', $borrowing->return_date?->toDateString());
        $this->assertSame(2000, $borrowing->total_fine);
        $this->assertSame(3, $book->fresh()->stock);
    }

    public function test_completed_borrowings_cannot_open_the_return_form(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create();
        $borrowing = Borrowing::factory()->for($member)->returned()->create();

        $response = $this->actingAs($admin)->get(route('admin.borrowings.return', $borrowing));

        $response->assertNotFound();
    }

    public function test_member_cannot_have_more_than_three_active_books(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $member = Member::factory()->create();
        $existingBooks = Book::factory()->count(2)->for($category)->create([
            'stock' => 5,
        ]);

        $activeBorrowing = Borrowing::factory()->for($member)->create();
        foreach ($existingBooks as $book) {
            BorrowingItem::factory()->for($activeBorrowing)->for($book)->create([
                'qty' => 1,
            ]);
        }

        $newBook = Book::factory()->for($category)->create([
            'stock' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.borrowings.create'))
            ->post(route('admin.borrowings.store'), [
                'member_id' => $member->getKey(),
                'borrow_date' => now()->toDateString(),
                'items' => [
                    ['book_id' => $newBook->getKey(), 'qty' => 2],
                ],
            ]);

        $response->assertRedirect(route('admin.borrowings.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(5, $newBook->fresh()->stock);
    }

    public function test_member_cannot_borrow_same_book_while_previous_borrowing_is_active(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $member = Member::factory()->create();
        $book = Book::factory()->for($category)->create([
            'title' => 'Laravel untuk Sistem Informasi',
            'stock' => 5,
        ]);

        $activeBorrowing = Borrowing::factory()->for($member)->create();
        BorrowingItem::factory()->for($activeBorrowing)->for($book)->create([
            'qty' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.borrowings.create'))
            ->post(route('admin.borrowings.store'), [
                'member_id' => $member->getKey(),
                'borrow_date' => now()->toDateString(),
                'items' => [
                    ['book_id' => $book->getKey(), 'qty' => 1],
                ],
            ]);

        $response->assertRedirect(route('admin.borrowings.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(5, $book->fresh()->stock);
    }

    public function test_borrowing_request_rejects_books_without_enough_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $member = Member::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.borrowings.create'))
            ->post(route('admin.borrowings.store'), [
                'member_id' => $member->getKey(),
                'borrow_date' => Carbon::now()->toDateString(),
                'items' => [
                    ['book_id' => $book->getKey(), 'qty' => 2],
                ],
            ]);

        $response->assertRedirect(route('admin.borrowings.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(1, $book->fresh()->stock);
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
