<x-layouts.app-layout
    title="Dashboard Anggota"
    heading="Dashboard anggota"
    description="Halaman ini merangkum status akun anggota, histori peminjaman, dan transaksi yang masih aktif agar alur pengguna sudah jelas sebelum fitur lain ditambah."
>
    @if (! $member)
        <section class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-6">
            <h3 class="text-lg font-semibold text-amber-900">Profil anggota belum tersedia</h3>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-amber-800">
                Akun ini sudah masuk sebagai anggota, tetapi record pada tabel `members` belum ditemukan.
                Cek proses registrasi atau seeder agar profil anggota ikut terbentuk.
            </p>
        </section>
    @else
        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-[1.75rem] border border-stone-200 bg-slate-950 p-6 text-white">
                <p class="text-sm font-medium text-slate-300">Kode anggota</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight">{{ $member->member_code }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-300">Identitas utama anggota yang dipakai untuk transaksi peminjaman.</p>
            </article>

            @foreach ($memberStats as $stat)
                <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-6">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $stat['description'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
            <h3 class="text-lg font-semibold text-slate-950">Riwayat peminjaman terbaru</h3>
            <p class="mt-1 text-sm text-slate-500">Daftar transaksi terbaru lengkap dengan status dan judul buku yang terlibat.</p>

            <div class="mt-6 space-y-4">
                @forelse ($borrowings as $borrowing)
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-teal-700">
                                        {{ $borrowing->status->value }}
                                    </span>
                                    <span class="text-sm text-slate-500">
                                        {{ $borrowing->borrow_date->format('d M Y') }} sampai {{ $borrowing->due_date->format('d M Y') }}
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($borrowing->borrowingItems as $item)
                                        <span class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-sm text-slate-600">
                                            {{ $item->book->title }} • {{ $item->qty }} eksemplar
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-sm text-slate-500">
                                <p>Denda: Rp {{ number_format($borrowing->total_fine, thousands_separator: '.') }}</p>
                                <p class="mt-1">
                                    @if ($borrowing->return_date)
                                        Dikembalikan {{ $borrowing->return_date->format('d M Y') }}
                                    @else
                                        Belum dikembalikan
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm leading-7 text-slate-500">
                        Belum ada riwayat peminjaman untuk akun ini.
                    </div>
                @endforelse
            </div>
        </section>
    @endif
</x-layouts.app-layout>
