<?php

namespace Tests\Feature\Anggota;

use App\BorrowingStatus;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_member_borrowing_history(): void
    {
        $this->get(route('anggota.borrowings.history'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_member_borrowing_history(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('anggota.borrowings.history'))
            ->assertForbidden();
    }

    public function test_member_only_sees_their_own_borrowing_history(): void
    {
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create([
            'stock' => 8,
        ]);
        $memberUser = User::factory()->create([
            'name' => 'Member Saya',
        ]);
        $member = Member::factory()->for($memberUser)->create([
            'member_code' => 'AGT-1001',
        ]);
        $otherMember = Member::factory()->for(User::factory()->state([
            'name' => 'Member Lain',
        ]))->create();

        $this->createBorrowingWithBook($member, $book, [
            'borrow_date' => '2026-04-04',
            'due_date' => '2026-04-11',
            'return_date' => '2026-04-13',
            'status' => BorrowingStatus::Dikembalikan,
            'total_fine' => 2000,
            'notes' => 'Histori Saya',
        ]);

        $this->createBorrowingWithBook($otherMember, $book, [
            'borrow_date' => '2026-04-05',
            'due_date' => '2026-04-12',
            'return_date' => null,
            'status' => BorrowingStatus::Dipinjam,
            'total_fine' => 0,
            'notes' => 'Histori Orang Lain',
        ]);

        $response = $this->actingAs($memberUser)->get(route('anggota.borrowings.history'));

        $response->assertOk()
            ->assertSee('Histori Saya')
            ->assertSee('Rp 2.000')
            ->assertDontSee('Histori Orang Lain')
            ->assertDontSee('Member Lain');
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
