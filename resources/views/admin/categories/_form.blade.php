@php
    $category ??= null;
    $method ??= 'POST';
    $submitLabel ??= 'Simpan kategori';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Nama kategori</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $category?->name) }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="80"
                required
            >
            @error('name')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="text-sm font-semibold text-slate-700">Slug</label>
            <input
                id="slug"
                type="text"
                name="slug"
                value="{{ old('slug', $category?->slug) }}"
                class="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10"
                maxlength="100"
                placeholder="Otomatis dibuat dari nama jika dikosongkan"
            >
            @error('slug')
                <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-slate-600">
        Gunakan nama yang singkat dan jelas karena kategori ini akan dipakai ulang pada form buku dan filter laporan.
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="submit"
            class="inline-flex items-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
            {{ $submitLabel }}
        </button>

        <a
            href="{{ route('admin.categories.index') }}"
            class="inline-flex items-center rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-stone-400 hover:bg-stone-50"
        >
            Kembali
        </a>
    </div>
</form>
