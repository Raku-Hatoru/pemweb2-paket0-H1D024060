<x-layouts.app-layout
    title="Edit Kategori"
    heading="Edit kategori buku"
    description="Perubahan kategori langsung berpengaruh ke data buku yang terhubung, jadi nama dan slug perlu tetap konsisten."
>
    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        @include('admin.categories._form', [
            'action' => route('admin.categories.update', $category),
            'method' => 'PUT',
            'category' => $category,
            'submitLabel' => 'Perbarui kategori',
        ])
    </section>
</x-layouts.app-layout>
