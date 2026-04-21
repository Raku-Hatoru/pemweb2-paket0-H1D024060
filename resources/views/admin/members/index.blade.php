<x-layouts.app-layout
    title="Data Anggota"
    heading="Kelola data anggota"
    description="Data anggota disimpan sebagai kombinasi akun login pada tabel `users` dan profil perpustakaan pada tabel `members`, sehingga transaksi peminjaman selalu punya konteks anggota yang jelas."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.members.create') }}"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            Tambah anggota
        </a>
    </x-slot:actions>

    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        <div class="overflow-hidden rounded-[1.5rem] border border-stone-200">
            <table class="min-w-full divide-y divide-stone-200 text-left">
                <thead class="bg-stone-50">
                    <tr class="text-sm font-semibold text-slate-700">
                        <th class="px-4 py-3">Anggota</th>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Kontak</th>
                        <th class="px-4 py-3">Peminjaman</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 bg-white text-sm text-slate-600">
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-950">{{ $member->user->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $member->user->email }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-950">{{ $member->member_code }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $member->updated_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ $member->phone ?: 'Belum diisi' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $member->address ?: 'Alamat belum diisi' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ $member->borrowings_count }} transaksi total</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $member->active_borrowings_count }} transaksi aktif • {{ (int) $member->active_books_count }} buku aktif
                                </p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.members.edit', $member) }}"
                                        class="rounded-full border border-stone-300 px-3 py-1.5 font-medium text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.members.destroy', $member) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-full border border-rose-200 px-3 py-1.5 font-medium text-rose-700 transition hover:bg-rose-50"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada data anggota. Tambahkan anggota agar transaksi peminjaman bisa dilakukan dengan konteks yang benar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="mt-6">
                {{ $members->links() }}
            </div>
        @endif
    </section>
</x-layouts.app-layout>
