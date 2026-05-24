@extends('layouts.guest')

@section('content')
    <div class="auth-page">
        <div class="auth-admin-strip">
            <a class="auth-admin-strip-link" href="{{ route('dashboard.index') }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Kembali ke ringkasan
            </a>
            <span class="auth-admin-strip-note">Hanya admin yang dapat membuat akun pengguna.</span>
        </div>
        <div class="auth-frame">
            <aside class="auth-aside" aria-hidden="true">
                <div class="auth-aside-content">
                    <div class="auth-aside-mark">
                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                    </div>
                    <p class="auth-aside-kicker">Panel admin</p>
                    <h1 class="auth-aside-title">Buat akun karyawan</h1>
                    <p class="auth-aside-text">Tambah akun untuk kasir atau admin lain. Kata sandi bisa diberikan langsung ke karyawan.</p>
                </div>
            </aside>

            <div class="auth-panel">
                <div class="auth-panel-inner">
                    <header class="auth-panel-head auth-panel-head--compact">
                        <h2 class="visually-hidden">Buat akun pengguna baru</h2>
                        <p class="auth-panel-lead">Isi data karyawan dan pilih peran akses ke sistem.</p>
                    </header>

                    <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="role" class="auth-label">Peran di sistem</label>
                            <select id="role" name="role" class="form-select auth-input auth-select" required>
                                <option value="kasir" @selected(old('role', 'kasir') === 'kasir')>Kasir — jual &amp; hutang</option>
                                <option value="admin" @selected(old('role') === 'admin')>Admin — kelola barang &amp; pengguna</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="name" class="auth-label">Nama lengkap</label>
                            <input id="name" type="text"
                                class="form-control auth-input @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name') }}" required autocomplete="name" autofocus
                                placeholder="Contoh: Budi Santoso">
                            @error('name')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="email" class="auth-label">Email</label>
                            <input id="email" type="email"
                                class="form-control auth-input @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
                            @error('email')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">Kata sandi</label>
                            <input id="password" type="password"
                                class="form-control auth-input @error('password') is-invalid @enderror" name="password"
                                required autocomplete="new-password" placeholder="Minimal 8 karakter">
                            @error('password')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password-confirm" class="auth-label">Ulangi kata sandi</label>
                            <input id="password-confirm" type="password" class="form-control auth-input"
                                name="password_confirmation" required autocomplete="new-password"
                                placeholder="Samakan dengan di atas">
                        </div>

                        <button type="submit" class="btn w-100 auth-submit">
                            Simpan akun
                        </button>
                    </form>
                </div>

                <p class="auth-footnote">Setelah disimpan, karyawan masuk di halaman <strong>login</strong> seperti biasa.</p>
            </div>
        </div>
    </div>
@endsection
