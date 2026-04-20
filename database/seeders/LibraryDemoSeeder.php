<?php

namespace Database\Seeders;

use App\BorrowingStatus;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            User::query()->updateOrCreate(
                ['email' => 'admin@perpus.test'],
                $this->attributesForUpsert(
                    User::factory()->demoAdmin()->make()
                )
            );

            $memberUser = User::query()->updateOrCreate(
                ['email' => 'anggota@perpus.test'],
                $this->attributesForUpsert(
                    User::factory()->demoMember()->make()
                )
            );

            $webCategory = Category::query()->updateOrCreate(
                ['slug' => 'pemrograman-web'],
                $this->attributesForUpsert(
                    Category::factory()->webProgramming()->make()
                )
            );

            $databaseCategory = Category::query()->updateOrCreate(
                ['slug' => 'basis-data'],
                $this->attributesForUpsert(
                    Category::factory()->database()->make()
                )
            );

            $laravelBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000011'],
                $this->attributesForUpsert(
                    Book::factory()->laravelInformationSystem()->make([
                        'category_id' => $webCategory->getKey(),
                    ])
                )
            );

            $databaseBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000012'],
                $this->attributesForUpsert(
                    Book::factory()->libraryDatabaseDesign()->make([
                        'category_id' => $databaseCategory->getKey(),
                    ])
                )
            );

            $member = Member::query()->updateOrCreate(
                ['member_code' => 'AGT-0001'],
                $this->attributesForUpsert(
                    Member::factory()->demo()->make([
                        'user_id' => $memberUser->getKey(),
                    ])
                )
            );

            DB::table('borrowings')->updateOrInsert(
                [
                    'member_id' => $member->getKey(),
                    'borrow_date' => '2026-04-10',
                ],
                [
                    'due_date' => '2026-04-17',
                    'return_date' => '2026-04-19',
                    'status' => BorrowingStatus::Dikembalikan->value,
                    'total_fine' => 2000,
                    'notes' => 'Contoh transaksi selesai',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('borrowings')->updateOrInsert(
                [
                    'member_id' => $member->getKey(),
                    'borrow_date' => '2026-04-18',
                ],
                [
                    'due_date' => '2026-04-25',
                    'return_date' => null,
                    'status' => BorrowingStatus::Dipinjam->value,
                    'total_fine' => 0,
                    'notes' => 'Contoh transaksi aktif',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $completedBorrowing = Borrowing::query()
                ->where('member_id', $member->getKey())
                ->where('borrow_date', '2026-04-10')
                ->firstOrFail();

            $activeBorrowing = Borrowing::query()
                ->where('member_id', $member->getKey())
                ->where('borrow_date', '2026-04-18')
                ->firstOrFail();

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $completedBorrowing->getKey(),
                    'book_id' => $laravelBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $activeBorrowing->getKey(),
                    'book_id' => $databaseBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesForUpsert(Model $model): array
    {
        return collect($model->getAttributes())
            ->except([
                $model->getKeyName(),
                $model->getCreatedAtColumn(),
                $model->getUpdatedAtColumn(),
            ])
            ->all();
    }
}
