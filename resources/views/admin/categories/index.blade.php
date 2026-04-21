<x-layouts.app-layout
    title="Kategori Buku"
    heading="Kelola kategori buku"
    description="CRUD kategori disiapkan lebih dulu karena menjadi fondasi saat admin menambah buku dan saat laporan membutuhkan filter berbasis kategori."
>
    <x-slot:actions>
        <a
            href="{{ route('admin.categories.create') }}"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            Tambah kategori
        </a>
    </x-slot:actions>

    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        <div class="overflow-hidden rounded-[1.5rem] border border-stone-200">
            <table class="min-w-full divide-y divide-stone-200 text-left">
                <thead class="bg-stone-50">
                    <tr class="text-sm font-semibold text-slate-700">
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Jumlah Buku</th>
                        <th class="px-4 py-3">Terakhir Diubah</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 bg-white text-sm text-slate-600">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-950">{{ $category->name }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $category->slug }}</td>
                            <td class="px-4 py-4">{{ $category->books_count }} buku</td>
                            <td class="px-4 py-4">{{ $category->updated_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="rounded-full border border-stone-300 px-3 py-1.5 font-medium text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
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
                                Belum ada kategori. Tambahkan kategori pertama untuk mulai menyusun data buku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif
    </section>
</x-layouts.app-layout>
