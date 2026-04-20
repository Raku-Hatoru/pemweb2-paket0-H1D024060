<?php

namespace Database\Seeders;

use App\BorrowingStatus;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LibraryDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@perpus.test'],
                [
                    'name' => 'Admin Perpustakaan',
                    'password' => Hash::make('password123'),
                    'role' => UserRole::Admin->value,
                    'email_verified_at' => now(),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('users')->updateOrInsert(
                ['email' => 'anggota@perpus.test'],
                [
                    'name' => 'Anggota Demo',
                    'password' => Hash::make('password123'),
                    'role' => UserRole::Anggota->value,
                    'email_verified_at' => now(),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('categories')->updateOrInsert(
                ['slug' => 'pemrograman-web'],
                [
                    'name' => 'Pemrograman Web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('categories')->updateOrInsert(
                ['slug' => 'basis-data'],
                [
                    'name' => 'Basis Data',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $webCategoryId = DB::table('categories')->where('slug', 'pemrograman-web')->value('id');
            $databaseCategoryId = DB::table('categories')->where('slug', 'basis-data')->value('id');

            DB::table('books')->updateOrInsert(
                ['isbn' => '9786020000011'],
                [
                    'category_id' => $webCategoryId,
                    'title' => 'Laravel untuk Sistem Informasi',
                    'author' => 'Tim Dosen Teknik',
                    'publisher' => 'FT Press',
                    'year_published' => 2024,
                    'stock' => 5,
                    'cover_image' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('books')->updateOrInsert(
                ['isbn' => '9786020000012'],
                [
                    'category_id' => $databaseCategoryId,
                    'title' => 'Desain Basis Data Perpustakaan',
                    'author' => 'Laboratorium Data',
                    'publisher' => 'FT Press',
                    'year_published' => 2023,
                    'stock' => 3,
                    'cover_image' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $memberUserId = DB::table('users')->where('email', 'anggota@perpus.test')->value('id');

            DB::table('members')->updateOrInsert(
                ['member_code' => 'AGT-0001'],
                [
                    'user_id' => $memberUserId,
                    'phone' => '081234567890',
                    'address' => 'Purwokerto',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $memberId = DB::table('members')->where('member_code', 'AGT-0001')->value('id');
            $firstBookId = DB::table('books')->where('isbn', '9786020000011')->value('id');
            $secondBookId = DB::table('books')->where('isbn', '9786020000012')->value('id');

            DB::table('borrowings')->updateOrInsert(
                [
                    'member_id' => $memberId,
                    'borrow_date' => '2026-04-10',
                ],
                [
                    'due_date' => '2026-04-17',
                    'return_date' => '2026-04-19',
                    'status' => BorrowingStatus::Dikembalikan->value,
                    'total_fine' => 2000,
                    'notes' => 'Contoh transaksi selesai',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('borrowings')->updateOrInsert(
                [
                    'member_id' => $memberId,
                    'borrow_date' => '2026-04-18',
                ],
                [
                    'due_date' => '2026-04-25',
                    'return_date' => null,
                    'status' => BorrowingStatus::Dipinjam->value,
                    'total_fine' => 0,
                    'notes' => 'Contoh transaksi aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $completedBorrowingId = DB::table('borrowings')
                ->where('member_id', $memberId)
                ->where('borrow_date', '2026-04-10')
                ->value('id');

            $activeBorrowingId = DB::table('borrowings')
                ->where('member_id', $memberId)
                ->where('borrow_date', '2026-04-18')
                ->value('id');

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $completedBorrowingId,
                    'book_id' => $firstBookId,
                ],
                [
                    'qty' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('borrowing_items')->updateOrInsert(
                [
                    'borrowing_id' => $activeBorrowingId,
                    'book_id' => $secondBookId,
                ],
                [
                    'qty' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });
    }
}
