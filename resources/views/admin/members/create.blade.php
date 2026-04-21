<x-layouts.app-layout
    title="Tambah Anggota"
    heading="Tambah anggota baru"
    description="Buat akun anggota baru sekaligus dengan profil perpustakaannya, supaya data peminjaman nanti langsung menempel ke entitas anggota yang benar."
>
    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        @include('admin.members._form', [
            'action' => route('admin.members.store'),
            'generatedMemberCode' => $generatedMemberCode,
            'submitLabel' => 'Simpan anggota',
        ])
    </section>
</x-layouts.app-layout>
