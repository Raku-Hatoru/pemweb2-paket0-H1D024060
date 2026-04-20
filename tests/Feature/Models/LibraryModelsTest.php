<?php

namespace Tests\Feature\Models;

use App\BorrowingStatus;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use App\UserRole;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_member_and_borrowings_relationships_are_available(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->for($user)->create();
        $borrowing = Borrowing::factory()->for($member)->create();

        $user->load(['member', 'borrowings']);

        $this->assertTrue($user->member->is($member));
        $this->assertTrue($user->borrowings->first()->is($borrowing));
        $this->assertSame(UserRole::Anggota, $user->fresh()->role);
    }

    public function test_category_and_book_relationships_are_available(): void
    {
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create();

        $book->load('category');
        $category->load('books');

        $this->assertTrue($book->category->is($category));
        $this->assertTrue($category->books->first()->is($book));
        $this->assertIsInt($book->stock);
        $this->assertIsInt($book->year_published);
    }

    public function test_borrowing_and_borrowing_item_relationships_and_casts_are_available(): void
    {
        $borrowing = Borrowing::factory()->returned()->create();
        $book = Book::factory()->create();
        $item = BorrowingItem::factory()->for($borrowing)->for($book)->create([
            'qty' => 2,
        ]);

        $borrowing->load(['member.borrowingItems', 'books', 'borrowingItems']);
        $book->load('borrowings');

        $this->assertSame(BorrowingStatus::Dikembalikan, $borrowing->status);
        $this->assertInstanceOf(CarbonInterface::class, $borrowing->borrow_date);
        $this->assertInstanceOf(CarbonInterface::class, $borrowing->due_date);
        $this->assertInstanceOf(CarbonInterface::class, $borrowing->return_date);
        $this->assertSame(2000, $borrowing->total_fine);
        $this->assertTrue($borrowing->books->first()->is($book));
        $this->assertTrue($book->borrowings->first()->is($borrowing));
        $this->assertTrue($borrowing->member->borrowingItems->first()->is($item));
        $this->assertSame(2, $item->fresh()->qty);
    }
}
