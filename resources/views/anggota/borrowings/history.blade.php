<x-layouts.app-layout
    title="Riwayat Peminjaman"
    heading="Riwayat peminjaman saya"
    description="Halaman ini hanya menampilkan transaksi milik akun yang sedang login, sehingga anggota bisa mengecek status pinjaman, denda, dan riwayat pengembalian tanpa melihat data anggota lain."
>
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
            <p class="text-sm font-medium text-slate-500">Total transaksi</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['total_transactions'] }}</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Seluruh transaksi peminjaman atas nama {{ $member->member_code }}.</p>
        </article>

        <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
            <p class="text-sm font-medium text-slate-500">Masih aktif</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['active_transactions'] }}</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Transaksi yang belum memiliki tanggal pengembalian.</p>
        </article>

        <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
            <p class="text-sm font-medium text-slate-500">Sudah selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['returned_transactions'] }}</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Riwayat peminjaman yang sudah dikembalikan.</p>
        </article>

        <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
            <p class="text-sm font-medium text-slate-500">Total denda</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                Rp {{ number_format($summary['total_fine'], thousands_separator: '.') }}
            </p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Akumulasi denda dari seluruh histori peminjaman.</p>
        </article>
    </section>

    <section class="space-y-4">
        @forelse ($borrowings as $borrowing)
            @php
                $displayStatus = $borrowing->displayStatus();
                $statusClasses = match ($displayStatus) {
                    \App\BorrowingStatus::Dikembalikan => 'bg-emerald-100 text-emerald-700',
                    \App\BorrowingStatus::Terlambat => 'bg-amber-100 text-amber-800',
                    default => 'bg-teal-100 text-teal-700',
                };
                $lateDays = $borrowing->canBeReturned() ? $borrowing->lateDaysFor(now()) : 0;
            @endphp

            <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-950">Transaksi #{{ $borrowing->id }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] {{ $statusClasses }}">
                                {{ $displayStatus->value }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Pinjam {{ $borrowing->borrow_date->format('d M Y') }} / Jatuh tempo {{ $borrowing->due_date->format('d M Y') }}
                        </p>
                        @if ($borrowing->notes)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $borrowing->notes }}</p>
                        @endif
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[420px]">
                        <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Jumlah buku</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ (int) $borrowing->total_books }} buku</p>
                        </div>
                        <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Denda</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">
                                Rp {{ number_format($borrowing->total_fine, thousands_separator: '.') }}
                            </p>
                        </div>
                        <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Pengembalian</p>
                            <p class="mt-2 text-sm font-semibold text-slate-950">
                                @if ($borrowing->return_date)
                                    {{ $borrowing->return_date->format('d M Y') }}
                                @else
                                    Belum dikembalikan
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if ($lateDays > 0)
                    <div class="mt-5 rounded-3xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Pinjaman ini terlambat {{ $lateDays }} hari. Estimasi denda saat ini:
                        Rp {{ number_format($borrowing->fineFor(now()), thousands_separator: '.') }}.
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($borrowing->borrowingItems as $item)
                        <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-1.5 text-sm text-slate-600">
                            {{ $item->book->title }} / {{ $item->qty }} eksemplar
                        </span>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-stone-300 bg-stone-50 p-8 text-sm leading-7 text-slate-500">
                Belum ada riwayat peminjaman untuk akun ini.
            </div>
        @endforelse

        @if ($borrowings->hasPages())
            <div class="pt-2">
                {{ $borrowings->links() }}
            </div>
        @endif
    </section>
</x-layouts.app-layout>
