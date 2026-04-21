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
            $admin = User::query()->updateOrCreate(
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

            $presentationUser = User::query()->updateOrCreate(
                ['email' => 'anggota2@perpus.test'],
                $this->attributesForUpsert(
                    User::factory()->demoMember()->make([
                        'name' => 'Anggota Presentasi',
                        'email' => 'anggota2@perpus.test',
                    ])
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

            $managementCategory = Category::query()->updateOrCreate(
                ['slug' => 'manajemen-perpustakaan'],
                $this->attributesForUpsert(
                    Category::factory()->make([
                        'name' => 'Manajemen Perpustakaan',
                        'slug' => 'manajemen-perpustakaan',
                    ])
                )
            );

            $laravelBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000011'],
                $this->attributesForUpsert(
                    Book::factory()->laravelInformationSystem()->make([
                        'category_id' => $webCategory->getKey(),
                        'stock' => 5,
                    ])
                )
            );

            $databaseBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000012'],
                $this->attributesForUpsert(
                    Book::factory()->libraryDatabaseDesign()->make([
                        'category_id' => $databaseCategory->getKey(),
                        'stock' => 1,
                    ])
                )
            );

            $uiBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000013'],
                $this->attributesForUpsert(
                    Book::factory()->make([
                        'category_id' => $webCategory->getKey(),
                        'isbn' => '9786020000013',
                        'title' => 'UI Sistem Informasi Kampus',
                        'author' => 'Studio Interaksi',
                        'publisher' => 'FT Press',
                        'year_published' => 2025,
                        'stock' => 2,
                        'cover_image' => null,
                    ])
                )
            );

            $managementBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000014'],
                $this->attributesForUpsert(
                    Book::factory()->make([
                        'category_id' => $managementCategory->getKey(),
                        'isbn' => '9786020000014',
                        'title' => 'Operasional Perpustakaan Modern',
                        'author' => 'Ruang Referensi',
                        'publisher' => 'FT Press',
                        'year_published' => 2022,
                        'stock' => 0,
                        'cover_image' => null,
                    ])
                )
            );

            $analysisBook = Book::query()->updateOrCreate(
                ['isbn' => '9786020000015'],
                $this->attributesForUpsert(
                    Book::factory()->make([
                        'category_id' => $managementCategory->getKey(),
                        'isbn' => '9786020000015',
                        'title' => 'Analisis Sistem Perpustakaan',
                        'author' => 'Tim Laboratorium',
                        'publisher' => 'FT Press',
                        'year_published' => 2024,
                        'stock' => 3,
                        'cover_image' => null,
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

            $presentationMember = Member::query()->updateOrCreate(
                ['member_code' => 'AGT-0002'],
                $this->attributesForUpsert(
                    Member::factory()->make([
                        'user_id' => $presentationUser->getKey(),
                        'member_code' => 'AGT-0002',
                        'phone' => '081234560002',
                        'address' => 'Purwokerto Utara',
                    ])
                )
            );

            $lateBorrowDate = now()->startOfMonth()->addDays(3);
            $lateDueDate = $lateBorrowDate->copy()->addDays(7);
            $lateReturnDate = $lateDueDate->copy()->addDays(2);

            $onTimeBorrowDate = now()->startOfMonth()->addDays(7);
            $onTimeDueDate = $onTimeBorrowDate->copy()->addDays(7);
            $onTimeReturnDate = $onTimeDueDate->copy();

            $activeBorrowDate = now()->subDays(3);
            $activeDueDate = $activeBorrowDate->copy()->addDays(7);

            $overdueBorrowDate = now()->subDays(11);
            $overdueDueDate = $overdueBorrowDate->copy()->addDays(7);
            $overdueFine = $overdueDueDate->diffInDays(now()) * 1000;

            $completedLateBorrowing = Borrowing::query()->updateOrCreate(
                [
                    'member_id' => $member->getKey(),
                    'borrow_date' => $lateBorrowDate->toDateString(),
                ],
                [
                    'due_date' => $lateDueDate->toDateString(),
                    'return_date' => $lateReturnDate->toDateString(),
                    'status' => BorrowingStatus::Dikembalikan->value,
                    'total_fine' => 2000,
                    'notes' => 'Demo laporan: selesai terlambat 2 hari.',
                ]
            );

            $completedOnTimeBorrowing = Borrowing::query()->updateOrCreate(
                [
                    'member_id' => $presentationMember->getKey(),
                    'borrow_date' => $onTimeBorrowDate->toDateString(),
                ],
                [
                    'due_date' => $onTimeDueDate->toDateString(),
                    'return_date' => $onTimeReturnDate->toDateString(),
                    'status' => BorrowingStatus::Dikembalikan->value,
                    'total_fine' => 0,
                    'notes' => 'Demo laporan: selesai tepat waktu.',
                ]
            );

            $activeBorrowing = Borrowing::query()->updateOrCreate(
                [
                    'member_id' => $member->getKey(),
                    'borrow_date' => $activeBorrowDate->toDateString(),
                ],
                [
                    'due_date' => $activeDueDate->toDateString(),
                    'return_date' => null,
                    'status' => BorrowingStatus::Dipinjam->value,
                    'total_fine' => 0,
                    'notes' => 'Demo riwayat: masih dipinjam dan belum jatuh tempo.',
                ]
            );

            $overdueBorrowing = Borrowing::query()->updateOrCreate(
                [
                    'member_id' => $presentationMember->getKey(),
                    'borrow_date' => $overdueBorrowDate->toDateString(),
                ],
                [
                    'due_date' => $overdueDueDate->toDateString(),
                    'return_date' => null,
                    'status' => BorrowingStatus::Terlambat->value,
                    'total_fine' => $overdueFine,
                    'notes' => 'Demo pengembalian: terlambat dan siap diproses real-time.',
                ]
            );

            $timestamp = now();

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $completedLateBorrowing->getKey(),
                    'book_id' => $laravelBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $completedOnTimeBorrowing->getKey(),
                    'book_id' => $analysisBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $activeBorrowing->getKey(),
                    'book_id' => $databaseBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $activeBorrowing->getKey(),
                    'book_id' => $uiBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $overdueBorrowing->getKey(),
                    'book_id' => $managementBook->getKey(),
                ],
                [
                    'qty' => 1,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
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
