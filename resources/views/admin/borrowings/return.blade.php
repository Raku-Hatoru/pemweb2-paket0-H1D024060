@php
    $selectedReturnDate = old('return_date', $defaultReturnDate);
    $selectedReturnCarbon = \Illuminate\Support\Carbon::parse($selectedReturnDate);
    $selectedLateDays = $borrowing->lateDaysFor($selectedReturnCarbon);
    $selectedFine = $borrowing->fineFor($selectedReturnCarbon);
@endphp

<x-layouts.app-layout
    title="Pengembalian Buku"
    heading="Proses pengembalian buku"
    description="Denda dihitung real-time dengan rumus Rp 1.000 x hari terlambat. Saat transaksi disimpan, sistem akan mengisi return_date, mengubah status pinjaman, dan mengembalikan stok buku di dalam database transaction."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.borrowings.index') }}"
            class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
        >
            Kembali ke riwayat
        </a>
    </x-slot:actions>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">Data transaksi</p>
                    <h3 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">{{ $borrowing->member->user->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        {{ $borrowing->member->member_code }} / {{ $borrowing->member->user->email }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Tanggal pinjam</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $borrowing->borrow_date->format('d M Y') }}</p>
                    </div>
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Jatuh tempo</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $borrowing->due_date->format('d M Y') }}</p>
                    </div>
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Total buku</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $borrowing->borrowingItems->sum('qty') }} buku</p>
                    </div>
                </div>
            </div>

            @if ($borrowing->notes)
                <div class="mt-5 rounded-3xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-slate-600">
                    <span class="font-semibold text-slate-950">Catatan transaksi:</span>
                    {{ $borrowing->notes }}
                </div>
            @endif

            <div class="mt-6">
                <h4 class="text-lg font-semibold text-slate-950">Buku yang dikembalikan</h4>
                <div class="mt-4 space-y-3">
                    @foreach ($borrowing->borrowingItems as $item)
                        <div class="flex items-center justify-between gap-4 rounded-3xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <div>
                                <p class="font-medium text-slate-950">{{ $item->book->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">Stok tersedia saat ini: {{ $item->book->stock }}</p>
                            </div>
                            <span class="rounded-full border border-stone-200 bg-white px-3 py-1 text-sm font-semibold text-slate-700">
                                {{ $item->qty }} eksemplar
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
            <form
                method="POST"
                action="{{ route('admin.borrowings.return.store', $borrowing) }}"
                class="space-y-6"
                data-return-form
                data-due-date="{{ $borrowing->due_date->toDateString() }}"
                data-daily-fine="1000"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label for="return_date" class="text-sm font-semibold text-slate-700">Tanggal pengembalian</label>
                    <input
                        id="return_date"
                        type="date"
                        name="return_date"
                        value="{{ $selectedReturnDate }}"
                        min="{{ $borrowing->borrow_date->toDateString() }}"
                        class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                        data-return-date
                        required
                    >
                    @error('return_date')
                        <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-slate-500">
                        Pilih tanggal aktual pengembalian. Sistem akan menghitung denda otomatis berdasarkan selisih dari jatuh tempo.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Hari terlambat</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            <span data-late-days>{{ $selectedLateDays }}</span>
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Nilai ini akan berubah langsung saat tanggal pengembalian diganti.</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Estimasi denda</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950" data-fine-display>
                            Rp {{ number_format($selectedFine, thousands_separator: '.') }}
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Rumus: Rp 1.000 x jumlah hari terlambat.</p>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-slate-600">
                    Saat tombol disimpan ditekan, sistem menjalankan proses pengembalian di dalam
                    <code>DB::transaction()</code> supaya <code>return_date</code>, <code>status</code>,
                    <code>total_fine</code>, dan stok buku berubah secara konsisten.
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Simpan pengembalian
                    </button>

                    <a
                        href="{{ route('admin.borrowings.index') }}"
                        class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </article>
    </section>
</x-layouts.app-layout>
