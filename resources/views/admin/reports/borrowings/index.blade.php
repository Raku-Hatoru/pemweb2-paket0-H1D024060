<x-layouts.app-layout
    title="Laporan Peminjaman"
    heading="Laporan peminjaman per bulan"
    description="Gunakan filter bulan atau rentang tanggal untuk menyusun laporan demo, lalu unduh hasilnya ke PDF langsung dari halaman ini."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.reports.borrowings.pdf', request()->query()) }}"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            Export PDF
        </a>
        <a
            href="{{ route('admin.reports.borrowings') }}"
            class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
        >
            Reset filter
        </a>
    </x-slot:actions>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">Filter laporan</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Atur periode laporan</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">
                    Pakai filter bulan untuk demo cepat, atau rentang tanggal saat dosen meminta data custom.
                </p>
            </div>

            <form method="GET" action="{{ route('admin.reports.borrowings') }}" class="mt-6 space-y-5">
                <div>
                    <label for="month" class="text-sm font-semibold text-slate-700">Filter bulan</label>
                    <input
                        id="month"
                        type="month"
                        name="month"
                        value="{{ request('month') }}"
                        class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                    >
                    @error('month')
                        <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="date_from" class="text-sm font-semibold text-slate-700">Dari tanggal</label>
                        <input
                            id="date_from"
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                        >
                        @error('date_from')
                            <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_to" class="text-sm font-semibold text-slate-700">Sampai tanggal</label>
                        <input
                            id="date_to"
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                        >
                        @error('date_to')
                            <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-slate-600">
                    Periode aktif:
                    <span class="font-semibold text-slate-950">{{ $periodLabel }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Terapkan filter
                    </button>

                    <a
                        href="{{ route('admin.reports.borrowings.pdf', request()->query()) }}"
                        class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
                    >
                        Download PDF
                    </a>
                </div>
            </form>
        </article>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
                <p class="text-sm font-medium text-slate-500">Total transaksi</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['total_transactions'] }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">Seluruh transaksi peminjaman pada periode terpilih.</p>
            </article>

            <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
                <p class="text-sm font-medium text-slate-500">Total buku dipinjam</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['total_books'] }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">Akumulasi eksemplar yang tercatat dalam transaksi.</p>
            </article>

            <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
                <p class="text-sm font-medium text-slate-500">Transaksi selesai</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $summary['returned_transactions'] }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">Transaksi yang sudah memiliki tanggal pengembalian.</p>
            </article>

            <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
                <p class="text-sm font-medium text-slate-500">Total denda</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                    Rp {{ number_format($summary['total_fine'], thousands_separator: '.') }}
                </p>
                <p class="mt-3 text-sm leading-6 text-slate-600">Nilai denda yang sudah tercatat pada periode laporan.</p>
            </article>
        </section>
    </section>

    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-slate-950">Detail laporan</h3>
                <p class="mt-1 text-sm text-slate-500">Data diurutkan dari tanggal pinjam terbaru agar mudah dipresentasikan.</p>
            </div>
            <p class="text-sm font-medium text-slate-500">Periode: {{ $periodLabel }}</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="table-auto min-w-full divide-y divide-stone-200">
                <thead>
                    <tr class="text-left text-sm font-semibold text-slate-700">
                        <th class="pb-3 pr-4">Anggota</th>
                        <th class="pb-3 pr-4">Periode pinjam</th>
                        <th class="pb-3 pr-4">Buku</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Total buku</th>
                        <th class="pb-3 pr-4">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($borrowings as $borrowing)
                        @php
                            $displayStatus = $borrowing->displayStatus();
                            $statusClasses = match ($displayStatus) {
                                \App\BorrowingStatus::Dikembalikan => 'bg-emerald-100 text-emerald-700',
                                \App\BorrowingStatus::Terlambat => 'bg-amber-100 text-amber-800',
                                default => 'bg-teal-100 text-teal-700',
                            };
                        @endphp
                        <tr class="align-top text-sm text-slate-600">
                            <td class="py-4 pr-4">
                                <p class="font-semibold text-slate-950">{{ $borrowing->member->user->name }}</p>
                                <p class="mt-1 text-slate-500">{{ $borrowing->member->member_code }}</p>
                            </td>
                            <td class="py-4 pr-4">
                                <p>{{ $borrowing->borrow_date->format('d M Y') }}</p>
                                <p class="mt-1 text-slate-500">Jatuh tempo {{ $borrowing->due_date->format('d M Y') }}</p>
                                <p class="mt-1 text-slate-500">
                                    @if ($borrowing->return_date)
                                        Kembali {{ $borrowing->return_date->format('d M Y') }}
                                    @else
                                        Belum dikembalikan
                                    @endif
                                </p>
                            </td>
                            <td class="py-4 pr-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($borrowing->borrowingItems as $item)
                                        <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-slate-600">
                                            {{ $item->book->title }}
                                        </span>
                                    @endforeach
                                </div>
                                @if ($borrowing->notes)
                                    <p class="mt-2 max-w-xs text-slate-500">{{ $borrowing->notes }}</p>
                                @endif
                            </td>
                            <td class="py-4 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] {{ $statusClasses }}">
                                    {{ $displayStatus->value }}
                                </span>
                            </td>
                            <td class="py-4 pr-4 font-semibold text-slate-950">{{ (int) $borrowing->total_books }}</td>
                            <td class="py-4 pr-4 font-semibold text-slate-950">
                                Rp {{ number_format($borrowing->total_fine, thousands_separator: '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-sm leading-7 text-slate-500">
                                Belum ada transaksi untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($borrowings->hasPages())
            <div class="pt-6">
                {{ $borrowings->links() }}
            </div>
        @endif
    </section>
</x-layouts.app-layout>
