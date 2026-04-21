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
}
