@extends('layouts.guest')

@section('content')
    <div class="auth-page">
        <div class="auth-frame">
            <aside class="auth-aside" aria-hidden="true">
                <div class="auth-aside-content">
                    <div class="auth-aside-mark">
                        <i class="fas fa-seedling" aria-hidden="true"></i>
                    </div>
                    <p class="auth-aside-kicker">Sistem informasi penjualan</p>
                    <h1 class="auth-aside-title">Toko Pupuk Sawiji Tani</h1>
                    <p class="auth-aside-text">Pencatatan transaksi dan stok yang rapi, siap dipakai setiap hari.</p>
                </div>
            </aside>

            <div class="auth-panel">
                <div class="auth-panel-inner">
                    <header class="auth-panel-head auth-panel-head--compact">
                        <h2 class="visually-hidden">Masuk ke sistem</h2>
                        <p class="auth-panel-lead">Masukkan email dan kata sandi akun Anda (admin atau kasir).</p>
                    </header>

                    <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="auth-label">Email</label>
                            <input id="email" type="email"
                                class="form-control auth-input @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                placeholder="nama@email.com">
                            @error('email')
                                <div class="invalid-feedback d-block" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">Kata sandi</label>
                            <div class="auth-input-wrap">
                                <input id="password" type="password"
                                    class="form-control auth-input @error('password') is-invalid @enderror"
                                    name="password" required autocomplete="current-password"
                                    placeholder="••••••••">
                                <button type="button" class="auth-toggle-pw" id="auth-toggle-pw"
                                    aria-label="Tampilkan atau sembunyikan kata sandi" aria-pressed="false">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="auth-row">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 auth-submit">
                            Masuk ke sistem
                        </button>
                    </form>
                </div>

                <p class="auth-footnote">Inventaris Pupuk · Kab. Sungai Bahar, Jambi</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pw = document.getElementById('password');
            const btn = document.getElementById('auth-toggle-pw');
            if (!pw || !btn) return;
            btn.addEventListener('click', function() {
                const show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });
    </script>
@endpush
