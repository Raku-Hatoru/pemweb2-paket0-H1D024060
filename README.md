# Perpustakaan Digital

Aplikasi Laravel untuk manajemen anggota, kategori, peminjaman multi-buku, pengembalian dengan denda real-time, laporan bulanan PDF, dan dashboard statistik.

## Prerequisites

- PHP 8.4+
- Composer
- Node.js 20+ dan npm
- SQLite
- Git

## Running the website locally

1. Install dependency backend dan frontend.

```bash
composer install
npm install
```

2. Siapkan file environment.

```bash
copy .env.example .env
php artisan key:generate
```

3. Pastikan database SQLite tersedia.

```bash
New-Item -ItemType File -Force database/database.sqlite
```

4. Jalankan migrasi dan seed demo.

```bash
php artisan migrate:fresh --seed
```

5. Jalankan Vite dan web server.

```bash
npm run dev
php artisan serve
```

6. Buka aplikasi di `http://127.0.0.1:8000`.

## Demo data

Seed demo sudah menyiapkan:

- Admin: `admin@perpus.test` / `password123`
- Anggota demo: `anggota@perpus.test` / `password123`
- Anggota presentasi: `anggota2@perpus.test` / `password123`

Data demo juga sudah mencakup:

- transaksi selesai tepat waktu
- transaksi selesai terlambat dengan denda
- transaksi aktif
- transaksi terlambat yang siap diproses dari halaman pengembalian
- stok buku yang sudah menyesuaikan transaksi aktif

## Troubleshooting

- Jika halaman tampil tanpa style, pastikan `npm run dev` masih berjalan.
- Jika muncul error Vite manifest, jalankan `npm run dev` atau `npm run build`.
- Jika muncul error `No application encryption key has been specified`, jalankan `php artisan key:generate`.
- Jika migrasi gagal karena SQLite, pastikan file `database/database.sqlite` benar-benar ada.
- Jika data demo terasa tidak sinkron, jalankan ulang `php artisan migrate:fresh --seed`.
- Jika route atau config terasa masih lama, bersihkan cache:

```bash
php artisan optimize:clear
```

## Architecture/Philosophies

- Role-based flow: admin mengelola anggota, kategori, peminjaman, pengembalian, laporan, dan dashboard; anggota hanya melihat dashboard dan riwayat miliknya sendiri.
- Transaction safety: pembuatan peminjaman dan pengembalian memakai `DB::transaction()` supaya stok buku dan status pinjaman tetap konsisten.
- Validation first: aturan stok, batas 3 buku aktif, dan validasi tanggal dipusatkan di Form Request.
- Thin controllers: controller fokus pada orkestrasi query dan response, sedangkan aturan domain ada di model seperti `Borrowing::fineFor()` dan `Borrowing::lateDaysFor()`.
- Version-friendly reporting: export PDF memakai generator internal project, jadi tidak perlu menambah dependency baru.
- Feature-test oriented: auth, CRUD, peminjaman, pengembalian, validasi stok, batas 3 buku, riwayat, dan laporan ditutup dengan feature test PHPUnit.

## Test commands

Contoh perintah yang paling relevan saat development:

```bash
php artisan test --compact tests/Feature/Auth
php artisan test --compact tests/Feature/Admin/CategoryManagementTest.php
php artisan test --compact tests/Feature/Admin/MemberManagementTest.php
php artisan test --compact tests/Feature/Admin/BorrowingManagementTest.php
php artisan test --compact tests/Feature/Admin/BorrowingReportTest.php
php artisan test --compact tests/Feature/Anggota/BorrowingHistoryTest.php
vendor/bin/pint --dirty --format agent
```
