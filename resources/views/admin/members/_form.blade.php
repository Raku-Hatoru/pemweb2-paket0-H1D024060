@php
    $member ??= null;
    $method ??= 'POST';
    $submitLabel ??= 'Simpan anggota';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Nama lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $member?->user->name) }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="100"
                required
            >
            @error('name')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $member?->user->email) }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="150"
                required
            >
            @error('email')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="member_code" class="text-sm font-semibold text-slate-700">Kode anggota</label>
            <input
                id="member_code"
                type="text"
                name="member_code"
                value="{{ old('member_code', $member?->member_code ?? $generatedMemberCode ?? '') }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="20"
                required
            >
            @error('member_code')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="text-sm font-semibold text-slate-700">Nomor telepon</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $member?->phone) }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="20"
            >
            @error('phone')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="password" class="text-sm font-semibold text-slate-700">
                Password {{ $member ? '(opsional bila tidak diganti)' : '' }}
            </label>
            <input
                id="password"
                type="password"
                name="password"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                {{ $member ? '' : 'required' }}
            >
            @error('password')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Konfirmasi password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                {{ $member ? '' : 'required' }}
            >
        </div>
    </div>

    <div>
        <label for="address" class="text-sm font-semibold text-slate-700">Alamat</label>
        <textarea
            id="address"
            name="address"
            rows="4"
            class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
        >{{ old('address', $member?->address) }}</textarea>
        @error('address')
            <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-slate-600">
        Data di form ini akan disimpan ke dua tabel sekaligus: identitas akun login pada `users`, lalu detail keanggotaan perpustakaan pada `members`.
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="submit"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            {{ $submitLabel }}
        </button>

        <a
            href="{{ route('admin.members.index') }}"
            class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
        >
            Kembali
        </a>
    </div>
</form>
