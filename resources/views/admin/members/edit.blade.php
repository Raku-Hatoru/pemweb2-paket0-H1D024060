<x-layouts.app-layout
    title="Edit Anggota"
    heading="Edit data anggota"
    description="Perbarui informasi akun dan profil anggota tanpa memutus relasi ke histori peminjaman yang sudah ada."
>
    <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6">
        @include('admin.members._form', [
            'action' => route('admin.members.update', $member),
            'method' => 'PUT',
            'member' => $member,
            'submitLabel' => 'Perbarui anggota',
        ])
    </section>
</x-layouts.app-layout>
