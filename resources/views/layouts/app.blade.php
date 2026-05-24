<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventaris Pupuk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @stack('styles')
</head>

<body
    class="ps-theme-warm {{ trim(implode(' ', array_filter([request()->is('products*') || request()->is('suppliers*') ? 'products-page' : null, request()->is('debts*') ? 'debts-page' : null, request()->is('reports') ? 'reports-page' : null, request()->is('sales') ? 'ps-page-sales' : null]))) }}">
    @auth
        @if (!request()->is('sales'))
        <header class="ps-global-navbar">
            <div class="ps-global-left">
                <button class="ps-sidebar-toggle" type="button" aria-label="Toggle sidebar" onclick="toggleSidebar()">
                    <i class="fas fa-bars-staggered"></i>
                </button>
                <div class="ps-global-brand">
                    <i class="fas fa-seedling"></i>
                    <span>Toko Pupuk</span>
                </div>
            </div>
            <div class="ps-global-actions">
                <div class="ps-user">
                    <div class="ps-user-text">
                        <div class="ps-user-name">{{ ucfirst(Auth::user()->role) }}: {{ Auth::user()->name }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ps-navbar-logout-form">
                    @csrf
                    <button type="submit" class="ps-navbar-logout">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>
        @endif
    @endauth

    <div class="ps-layout">
        @auth
            <aside class="ps-sidebar">
                <div class="ps-brand">
                    <div class="ps-brand-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="ps-brand-text">
                        <div class="ps-brand-title">Pos System</div>
                        <div class="ps-brand-subtitle">Sistem kasir</div>
                    </div>
                    <button class="ps-sidebar-collapse" type="button" aria-label="Toggle sidebar"
                        onclick="document.body.classList.toggle('ps-sidebar-collapsed')">
                        <i class="fas fa-angle-left"></i>
                    </button>
                </div>

                <nav class="ps-nav">
                    <div class="ps-nav-section">Menu</div>
                    <a class="ps-nav-item {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 13h8V3H3v10Zm10 8h8v-6h-8v6Zm0-18v10h8V3h-8ZM3 21h8v-6H3v6Z" fill="currentColor" />
                        </svg>
                        <span>Ringkasan Hari Ini</span>
                    </a>
                    <a class="ps-nav-item {{ request()->is('sales') ? 'active' : '' }}" href="/sales">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 4h16v4H4V4Zm0 6h16v10H4V10Zm3 3v4h4v-4H7Z" fill="currentColor" />
                        </svg>
                        <span>Kasir</span>
                    </a>

                    @if (Auth::user()->isAdmin())
                        <a class="ps-nav-item {{ request()->is('products*') ? 'active' : '' }}" href="/products">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 2 3 6.5v11L12 22l9-4.5v-11L12 2Zm0 2.2 6.7 3.3L12 10.8 5.3 7.5 12 4.2Z"
                                    fill="currentColor" />
                            </svg>
                            <span>Barang & Stok</span>
                        </a>
                        <a class="ps-nav-item {{ request()->is('suppliers*') ? 'active' : '' }}"
                            href="{{ route('suppliers.index') }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h10v2H4v-2Z" fill="currentColor" />
                            </svg>
                            <span>Supplier</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->isKasir())
                        <a class="ps-nav-item {{ request()->is('debts*') ? 'active' : '' }}"
                            href="{{ route('debts.index') }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Zm3 6h6v2H9V8Zm0 4h6v2H9v-2Z" fill="currentColor" />
                            </svg>
                            <span>Hutang Pelanggan</span>
                        </a>
                    @endif

                    <hr class="ps-nav-divider">

                    @if (Auth::user()->isAdmin() || Auth::user()->isManager())
                        <a class="ps-nav-item {{ request()->is('stock-reports') ? 'active' : '' }}" href="/stock-reports">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 4h8v7H3V4Zm10 0h8v7h-8V4ZM3 13h8v7H3v-7Zm10 0h8v7h-8v-7Z" fill="currentColor" />
                            </svg>
                            <span>Laporan Stok</span>
                        </a>
                        <a class="ps-nav-item {{ request()->is('reports') ? 'active' : '' }}" href="/reports">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5Z" fill="currentColor" />
                            </svg>
                            <span>Laporan Penjualan</span>
                        </a>
                    @endif
                </nav>

                <div class="ps-sidebar-footer">
                    <button type="button" class="ps-footer-item" onclick="showHelp()">
                        <i class="fas fa-question-circle"></i>
                        <span>Cara Menggunakan</span>
                    </button>
                    @if (Auth::user()->isAdmin())
                        <a class="ps-footer-item ps-footer-item--link {{ request()->is('register') ? 'is-active' : '' }}"
                            href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Buat akun</span>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="ps-logout-form">
                        @csrf
                        <button type="submit" class="ps-footer-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="ps-main">
                <main class="ps-content">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-circle-check me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        @else
            <main class="container py-4">
                @yield('content')
            </main>
        @endauth
    </div>

    <!-- Modal Bantuan -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-question-circle me-2 text-primary"></i>
                        Bantuan Sistem
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="help-content">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        function toggleSidebar() {
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                document.body.classList.toggle('ps-sidebar-mobile-open');
                return;
            }
            document.body.classList.toggle('ps-sidebar-collapsed');
        }

        function showHelp() {
            const role = '{{ Auth::user()->role ?? '' }}';
            let helpContent = '';

            if (role === 'admin') {
                helpContent = `
                    <h5><i class="fas fa-user-shield me-2 text-primary"></i>Bantuan untuk Admin</h5>
                    <p>Sebagai Admin, Anda memiliki akses penuh ke semua fitur:</p>
                    <ul>
                        <li><strong>Kelola Barang:</strong> Tambah, edit, hapus produk</li>
                        <li><strong>Jual Pupuk:</strong> Lakukan transaksi penjualan</li>
                        <li><strong>Lihat Laporan:</strong> Akses semua laporan</li>
                        <li><strong>Manajemen User:</strong> Kelola pengguna sistem</li>
                    </ul>
                `;
            } else if (role === 'kasir') {
                helpContent = `
                    <h5><i class="fas fa-cash-register me-2 text-success"></i>Bantuan untuk Kasir</h5>
                    <p>Sebagai Kasir, Anda dapat:</p>
                    <ul>
                        <li><strong>Jual Pupuk:</strong> Lakukan transaksi penjualan</li>
                        <li><strong>Lihat Produk:</strong> Cari dan lihat informasi produk</li>
                        <li><strong>Dashboard:</strong> Lihat ringkasan toko</li>
                    </ul>
                `;
            } else if (role === 'manager') {
                helpContent = `
                    <h5><i class="fas fa-chart-line me-2 text-info"></i>Bantuan untuk Manager</h5>
                    <p>Sebagai Manager, Anda dapat:</p>
                    <ul>
                        <li><strong>Lihat Laporan:</strong> Analisis penjualan dan stok</li>
                        <li><strong>Dashboard:</strong> Monitoring performa toko</li>
                        <li><strong>Analisis Bisnis:</strong> Lihat tren dan prediksi</li>
                    </ul>
                `;
            }

            const modal = new bootstrap.Modal(document.getElementById('helpModal'));
            document.getElementById('help-content').innerHTML = helpContent;
            modal.show();
        }
    </script>
</body>

</html>
