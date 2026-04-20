<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register | {{ config('app.name', 'Perpustakaan') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <style>
            :root {
                color-scheme: light;
                --ink: #172026;
                --muted: #5f6d78;
                --surface: rgba(255, 253, 249, 0.94);
                --field: #f6f0e8;
                --line: rgba(23, 32, 38, 0.12);
                --accent: #1c6b74;
                --accent-strong: #124850;
                --accent-soft: rgba(28, 107, 116, 0.12);
                --danger: #a82f2f;
                --danger-soft: #ffe8e1;
                --shadow: 0 30px 70px rgba(10, 22, 30, 0.18);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Instrument Sans', sans-serif;
                color: var(--ink);
                background:
                    radial-gradient(circle at top left, rgba(28, 107, 116, 0.28), transparent 32%),
                    radial-gradient(circle at bottom right, rgba(214, 151, 85, 0.26), transparent 30%),
                    linear-gradient(135deg, #f4eee3 0%, #ece4d7 48%, #f6f3ee 100%);
            }

            .page {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
            }

            .shell {
                width: min(100%, 1080px);
                display: grid;
                grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.92fr);
                background: var(--surface);
                border: 1px solid rgba(255, 255, 255, 0.55);
                border-radius: 30px;
                overflow: hidden;
                box-shadow: var(--shadow);
                backdrop-filter: blur(18px);
            }

            .hero {
                position: relative;
                padding: 52px;
                color: #f9fbfb;
                background:
                    linear-gradient(150deg, rgba(10, 46, 54, 0.94) 0%, rgba(19, 72, 80, 0.88) 55%, rgba(34, 102, 96, 0.78) 100%);
            }

            .hero::after {
                content: '';
                position: absolute;
                inset: auto -18% -22% auto;
                width: 280px;
                height: 280px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255, 230, 175, 0.38), rgba(255, 230, 175, 0));
                pointer-events: none;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.14);
                border: 1px solid rgba(255, 255, 255, 0.18);
                font-size: 0.92rem;
                font-weight: 600;
                letter-spacing: 0.02em;
            }

            .hero h1 {
                margin: 28px 0 18px;
                font-size: clamp(2.1rem, 4vw, 3.5rem);
                line-height: 1.02;
            }

            .hero p {
                margin: 0;
                max-width: 34rem;
                color: rgba(249, 251, 251, 0.82);
                font-size: 1.03rem;
                line-height: 1.7;
            }

            .hero-grid {
                margin-top: 34px;
                display: grid;
                gap: 16px;
            }

            .hero-card {
                padding: 18px 20px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.14);
            }

            .hero-card strong {
                display: block;
                margin-bottom: 8px;
                font-size: 0.96rem;
            }

            .hero-card span {
                color: rgba(249, 251, 251, 0.76);
                line-height: 1.6;
                font-size: 0.96rem;
            }

            .panel {
                padding: 44px;
                background: linear-gradient(180deg, rgba(255, 250, 243, 0.96) 0%, rgba(255, 254, 251, 0.98) 100%);
            }

            .panel-header h2 {
                margin: 0 0 10px;
                font-size: 2rem;
                line-height: 1.05;
            }

            .panel-header p {
                margin: 0;
                color: var(--muted);
                line-height: 1.7;
            }

            .alert {
                margin-top: 26px;
                padding: 14px 16px;
                border-radius: 16px;
                border: 1px solid rgba(168, 47, 47, 0.14);
                background: var(--danger-soft);
                color: var(--danger);
                font-size: 0.95rem;
                line-height: 1.55;
            }

            form {
                margin-top: 30px;
            }

            .field-group {
                display: grid;
                gap: 18px;
            }

            .field label {
                display: block;
                margin-bottom: 9px;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .field input {
                width: 100%;
                padding: 15px 16px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--field);
                color: var(--ink);
                font-size: 1rem;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            }

            .field input:focus {
                outline: none;
                border-color: rgba(28, 107, 116, 0.55);
                box-shadow: 0 0 0 4px rgba(28, 107, 116, 0.12);
                background: #fff;
            }

            .field small {
                display: block;
                margin-top: 8px;
                color: var(--muted);
                line-height: 1.5;
            }

            .submit {
                width: 100%;
                margin-top: 26px;
                padding: 16px 18px;
                border: none;
                border-radius: 18px;
                background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
                color: #f7fbfc;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: 0.02em;
                cursor: pointer;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                box-shadow: 0 18px 30px rgba(18, 72, 80, 0.2);
            }

            .submit:hover {
                transform: translateY(-1px);
                box-shadow: 0 22px 34px rgba(18, 72, 80, 0.24);
            }

            .submit:active {
                transform: translateY(0);
            }

            .panel-footer {
                margin-top: 22px;
                padding-top: 18px;
                border-top: 1px solid rgba(23, 32, 38, 0.08);
                color: var(--muted);
                font-size: 0.92rem;
                line-height: 1.7;
            }

            .panel-footer span {
                display: inline-block;
                margin-right: 12px;
                padding: 6px 10px;
                border-radius: 999px;
                background: var(--accent-soft);
                color: var(--accent-strong);
                font-weight: 700;
            }

            .text-link {
                color: var(--accent-strong);
                font-weight: 700;
                text-decoration: none;
            }

            .text-link:hover {
                text-decoration: underline;
            }

            @media (max-width: 920px) {
                .shell {
                    grid-template-columns: 1fr;
                }

                .hero,
                .panel {
                    padding: 34px 28px;
                }
            }

            @media (max-width: 560px) {
                .page {
                    padding: 14px;
                }

                .shell {
                    border-radius: 22px;
                }

                .hero,
                .panel {
                    padding: 28px 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="shell">
                <section class="hero">
                    <div class="eyebrow">Perpustakaan Digital</div>

                    <h1>Buat akun anggota baru dalam beberapa langkah singkat.</h1>
                    <p>
                        Form ini membuat data baru di tabel <strong>users</strong> dengan role default
                        <strong>anggota</strong>, lalu langsung masuk ke dashboard setelah registrasi berhasil.
                    </p>

                    <div class="hero-grid">
                        <div class="hero-card">
                            <strong>Role aman dan jelas</strong>
                            <span>Akun yang mendaftar dari halaman ini otomatis disimpan sebagai `anggota`, jadi alur admin tetap terpisah.</span>
                        </div>

                        <div class="hero-card">
                            <strong>Validasi siap pakai</strong>
                            <span>Nama, email unik, dan konfirmasi password diperiksa dulu sebelum akun dibuat ke database.</span>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Daftarkan akun</h2>
                        <p>Isi data berikut untuk membuat akun baru dan langsung memakai sistem perpustakaan.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf

                        <div class="field-group">
                            <div class="field">
                                <label for="name">Nama Lengkap</label>
                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    autocomplete="name"
                                    required
                                    autofocus
                                >
                                <small>Nama ini akan dipakai sebagai identitas akun di dashboard aplikasi.</small>
                            </div>

                            <div class="field">
                                <label for="email">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    required
                                >
                                <small>Gunakan email aktif karena nilainya harus unik pada tabel `users`.</small>
                            </div>

                            <div class="field">
                                <label for="password">Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                >
                                <small>Minimal 8 karakter dan harus mengandung huruf serta angka.</small>
                            </div>

                            <div class="field">
                                <label for="password_confirmation">Konfirmasi Password</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    required
                                >
                                <small>Ulangi password untuk memastikan tidak ada salah ketik saat daftar.</small>
                            </div>
                        </div>

                        <button class="submit" type="submit">Buat Akun dan Masuk</button>
                    </form>

                    <div class="panel-footer">
                        <span>Masuk</span>
                        Sudah punya akun?
                        <a class="text-link" href="{{ route('login') }}">Kembali ke halaman login</a>.
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
