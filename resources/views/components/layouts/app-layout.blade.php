@props([
    'title' => config('app.name', 'Perpustakaan'),
    'heading' => '',
    'description' => '',
])

@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $navigation = $user?->isAdmin()
        ? [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'patterns' => ['admin.dashboard']],
            ['label' => 'Kategori Buku', 'route' => 'admin.categories.index', 'patterns' => ['admin.categories.*']],
        ]
        : [
            ['label' => 'Dashboard', 'route' => 'anggota.dashboard', 'patterns' => ['anggota.dashboard']],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | {{ config('app.name', 'Perpustakaan') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(13,148,136,0.18),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.18),_transparent_26%),linear-gradient(180deg,_#f8f4ec_0%,_#f1ece2_45%,_#f8f7f3_100%)]">
            <div class="absolute inset-x-0 top-0 h-72 bg-[linear-gradient(135deg,rgba(15,118,110,0.16),transparent_58%)]"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 lg:flex-row lg:px-6">
                <aside class="w-full shrink-0 overflow-hidden rounded-[2rem] border border-white/70 bg-slate-950 text-white shadow-2xl shadow-slate-950/20 lg:sticky lg:top-6 lg:min-h-[calc(100vh-3rem)] lg:w-80">
                    <div class="flex h-full flex-col">
                        <div class="border-b border-white/10 px-6 py-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-teal-200/80">Perpustakaan Digital</p>
                            <h1 class="mt-3 text-2xl font-semibold leading-tight">Panel {{ $user?->isAdmin() ? 'Admin' : 'Anggota' }}</h1>
                            <p class="mt-3 text-sm leading-6 text-slate-300">
                                Alur utama sudah dipisah per role agar fitur berikutnya bisa ditambah tanpa mencampur hak akses.
                            </p>
                        </div>

                        <nav class="flex-1 space-y-2 px-4 py-5">
                            @foreach ($navigation as $item)
                                @php
                                    $isActive = collect($item['patterns'])->contains(fn (string $pattern): bool => request()->routeIs($pattern));
                                @endphp

                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition',
                                        'bg-white/12 text-white shadow-lg shadow-teal-500/10' => $isActive,
                                        'text-slate-300 hover:bg-white/6 hover:text-white' => ! $isActive,
                                    ])
                                >
                                    <span>{{ $item['label'] }}</span>
                                    <span class="rounded-full bg-white/8 px-2.5 py-1 text-[0.7rem] uppercase tracking-[0.2em] text-slate-200">
                                        {{ $isActive ? 'Aktif' : 'Buka' }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-auto border-t border-white/10 px-6 py-5">
                            <div class="rounded-3xl bg-white/8 p-4">
                                <p class="text-sm font-semibold">{{ $user?->name }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $user?->email }}</p>
                                <div class="mt-4 flex items-center justify-between gap-4">
                                    <span class="rounded-full bg-teal-400/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-teal-200">
                                        {{ $user?->role->value }}
                                    </span>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="rounded-full border border-white/12 px-3 py-1.5 text-sm font-medium text-slate-200 transition hover:border-white/30 hover:bg-white/8 hover:text-white"
                                        >
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="flex-1">
                    <div class="rounded-[2rem] border border-white/70 bg-white/80 shadow-xl shadow-stone-900/8 backdrop-blur">
                        <header class="border-b border-stone-200/80 px-6 py-6 lg:px-8">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="max-w-3xl">
                                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-teal-700">Workspace</p>
                                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $heading }}</h2>
                                    @if ($description !== '')
                                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">{{ $description }}</p>
                                    @endif
                                </div>

                                @isset($actions)
                                    <div class="flex flex-wrap items-center gap-3">
                                        {{ $actions }}
                                    </div>
                                @endisset
                            </div>
                        </header>

                        <div class="space-y-6 px-6 py-6 lg:px-8">
                            @if (session('status'))
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                                    {{ session('error') }}
                                </div>
                            @endif

                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
