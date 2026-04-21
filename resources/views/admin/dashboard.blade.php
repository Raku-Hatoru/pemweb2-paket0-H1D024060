<x-layouts.app-layout
    title="Dashboard Admin"
    heading="Dashboard admin perpustakaan"
    description="Akses admin difokuskan ke pengelolaan data inti. Dari sini alur kategori, buku, anggota, dan transaksi sudah punya titik masuk yang jelas."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.categories.create') }}"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            Tambah kategori
        </a>
    </x-slot:actions>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-[1.75rem] border border-stone-200 bg-stone-50/80 p-5">
                <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Peminjaman terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Ringkasan transaksi yang terakhir tercatat di sistem.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($recentBorrowings as $borrowing)
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $borrowing->member->user->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $borrowing->member->member_code }} • {{ $borrowing->borrow_date->format('d M Y') }} sampai {{ $borrowing->due_date->format('d M Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-teal-700">
                                    {{ $borrowing->status->value }}
                                </span>
                                <span class="text-sm font-medium text-slate-500">{{ (int) $borrowing->total_books }} buku</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm leading-7 text-slate-500">
                        Belum ada transaksi peminjaman yang tercatat.
                    </div>
                @endforelse
            </div>
        </article>

        <div class="space-y-6">
            <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
                <h3 class="text-lg font-semibold text-slate-950">Kategori terbaru</h3>
                <p class="mt-1 text-sm text-slate-500">Kategori ini langsung dipakai untuk input buku dan penyusunan filter data.</p>

                <div class="mt-6 space-y-3">
                    @forelse ($latestCategories as $category)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <div>
                                <p class="font-medium text-slate-950">{{ $category->name }}</p>
                                <p class="text-sm text-slate-500">{{ $category->slug }}</p>
                            </div>
                            <span class="text-sm font-medium text-slate-500">{{ $category->books_count }} buku</span>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-4 text-sm text-slate-500">
                            Belum ada kategori yang tersimpan.
                        </p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
                <h3 class="text-lg font-semibold text-slate-950">Anggota terbaru</h3>
                <p class="mt-1 text-sm text-slate-500">Akun anggota yang baru masuk ke sistem lewat registrasi atau seed demo.</p>

                <div class="mt-6 space-y-3">
                    @forelse ($recentMembers as $member)
                        <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <p class="font-medium text-slate-950">{{ $member->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $member->email }}</p>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-4 text-sm text-slate-500">
                            Belum ada anggota yang terdaftar.
                        </p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-layouts.app-layout>
