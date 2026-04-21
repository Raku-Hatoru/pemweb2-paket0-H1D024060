<x-layouts.app-layout
    title="Tambah Kategori"
    heading="Tambah kategori baru"
    description="Buat kategori yang nanti menjadi dasar pengelompokan buku. Slug boleh dikosongkan dan akan dibentuk otomatis dari nama."
>
    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        @include('admin.categories._form', [
            'action' => route('admin.categories.store'),
            'submitLabel' => 'Simpan kategori',
        ])
    </section>
</x-layouts.app-layout>
