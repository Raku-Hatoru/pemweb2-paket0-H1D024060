<x-layouts.app-layout
    title="Peminjaman Buku"
    heading="Kelola transaksi peminjaman"
    description="Admin dapat membuat peminjaman multi-buku untuk anggota. Sistem otomatis menghitung jatuh tempo 7 hari, membatasi maksimal 3 buku aktif, dan menjaga stok tetap konsisten."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.borrowings.create') }}"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            Buat peminjaman
        </a>
    </x-slot:actions>

    <section class="space-y-4">
        @forelse ($borrowings as $borrowing)
            <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-slate-950">{{ $borrowing->member->user->name }}</h3>
                            <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-teal-700">
                                {{ $borrowing->status->value }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ $borrowing->member->member_code }} • Pinjam {{ $borrowing->borrow_date->format('d M Y') }} • Jatuh tempo {{ $borrowing->due_date->format('d M Y') }}
                        </p>
                        @if ($borrowing->notes)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $borrowing->notes }}</p>
                        @endif
                    </div>

                    <div class="text-sm text-slate-500">
                        <p>{{ (int) $borrowing->total_books }} buku</p>
                        <p class="mt-1">Denda: Rp {{ number_format($borrowing->total_fine, thousands_separator: '.') }}</p>
                        <p class="mt-1">
                            @if ($borrowing->return_date)
                                Dikembalikan {{ $borrowing->return_date->format('d M Y') }}
                            @else
                                Belum dikembalikan
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($borrowing->borrowingItems as $item)
                        <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-1.5 text-sm text-slate-600">
                            {{ $item->book->title }} • {{ $item->qty }} eksemplar
                        </span>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-stone-300 bg-stone-50 p-8 text-sm leading-7 text-slate-500">
                Belum ada transaksi peminjaman. Gunakan tombol di atas untuk membuat transaksi baru.
            </div>
        @endforelse

        @if ($borrowings->hasPages())
            <div class="pt-2">
                {{ $borrowings->links() }}
            </div>
        @endif
    </section>
</x-layouts.app-layout>
