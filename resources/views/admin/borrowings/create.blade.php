@php
    $itemRows = old('items', [
        ['book_id' => '', 'qty' => 1],
        ['book_id' => '', 'qty' => 1],
        ['book_id' => '', 'qty' => 1],
    ]);
@endphp

<x-layouts.app-layout
    title="Buat Peminjaman"
    heading="Buat transaksi peminjaman"
    description="Pilih anggota dan maksimal tiga buku aktif. Buku dengan stok 0 disembunyikan dari form, jatuh tempo dihitung otomatis 7 hari dari tanggal pinjam, dan judul yang masih aktif tidak boleh dipinjam ulang."
>
    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        <form method="POST" action="{{ route('admin.borrowings.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <label for="member_id" class="text-sm font-semibold text-slate-700">Anggota</label>
                    <select
                        id="member_id"
                        name="member_id"
                        class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                        required
                    >
                        <option value="">Pilih anggota</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>
                                {{ $member->member_code }} - {{ $member->user->name }} ({{ (int) $member->active_books_count }} buku aktif)
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="borrow_date" class="text-sm font-semibold text-slate-700">Tanggal pinjam</label>
                    <input
                        id="borrow_date"
                        type="date"
                        name="borrow_date"
                        value="{{ old('borrow_date', now()->toDateString()) }}"
                        class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                        required
                    >
                    @error('borrow_date')
                        <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-slate-500">Jatuh tempo dihitung otomatis 7 hari setelah tanggal pinjam.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Daftar buku</h3>
                        <p class="mt-1 text-sm text-slate-500">Sediakan hingga tiga baris untuk pinjaman multi-buku. Baris kosong akan diabaikan saat disimpan.</p>
                    </div>
                </div>

                @error('items')
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        {{ $message }}
                    </div>
                @enderror

                <div class="space-y-4">
                    @foreach ($itemRows as $index => $item)
                        <div class="grid gap-4 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 lg:grid-cols-[1fr_180px]">
                            <div>
                                <label for="items_{{ $index }}_book_id" class="text-sm font-semibold text-slate-700">Buku {{ $loop->iteration }}</label>
                                <select
                                    id="items_{{ $index }}_book_id"
                                    name="items[{{ $index }}][book_id]"
                                    class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                                >
                                    <option value="">Pilih buku tersedia</option>
                                    @foreach ($availableBooks as $book)
                                        <option value="{{ $book->id }}" @selected((string) ($item['book_id'] ?? '') === (string) $book->id)>
                                            {{ $book->title }} - {{ $book->category->name }} (stok {{ $book->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="items_{{ $index }}_qty" class="text-sm font-semibold text-slate-700">Qty</label>
                                <input
                                    id="items_{{ $index }}_qty"
                                    type="number"
                                    name="items[{{ $index }}][qty]"
                                    value="{{ $item['qty'] ?? 1 }}"
                                    min="1"
                                    max="3"
                                    class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="notes" class="text-sm font-semibold text-slate-700">Catatan transaksi</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-slate-600">
                Validasi inti yang aktif pada form ini:
                anggota maksimal memiliki 3 buku aktif, judul yang masih dipinjam tidak boleh dipilih lagi, dan stok akan dikurangi di dalam `DB::transaction()` agar tetap konsisten.
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Simpan transaksi
                </button>

                <a
                    href="{{ route('admin.borrowings.index') }}"
                    class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
                >
                    Kembali
                </a>
            </div>
        </form>
    </section>
</x-layouts.app-layout>
