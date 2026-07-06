# Kesimpulan dan Saran — Skripsi Sistem Inventaris & Penjualan Pupuk

**Toko Pupuk Sawiji Tani** · Sistem hanya memakai peran **admin** dan **kasir** (tidak menggunakan manager).

---

## Kesimpulan

Penelitian ini berhasil merancang dan mengimplementasikan **Sistem Informasi Inventaris dan Penjualan Pupuk** berbasis web untuk Toko Pupuk Sawiji Tani. Sistem dibangun dengan Laravel 12, MySQL, dan antarmuka web berbasis pendekatan **Human-Centered Design (HCD)** dengan tema **Warm Neutral**, sehingga lebih mudah dibaca oleh pengguna dewasa (±50 tahun). Fitur utama meliputi login dengan dua peran pengguna (**admin** dan **kasir**), pengelolaan barang dan supplier (admin), transaksi kasir (POS), pencatatan piutang dan cicilan, laporan penjualan, laporan mutasi stok, dashboard ringkasan, serta penyesuaian stok.

Pengujian **black box** pada 103 skenario menunjukkan keluaran sistem sesuai spesifikasi: validasi input berjalan, transaksi dan stok konsisten, pembatasan hak akses antara admin dan kasir berfungsi, serta fitur laporan dan unduhan berkas dapat digunakan. Evaluasi antarmuka menunjukkan peningkatan keterbacaan melalui kontras warna, ukuran huruf, format angka ribuan, dan tampilan angka yang jelas bagi pengguna.

Secara keseluruhan, sistem ini **layak digunakan** untuk mendukung digitalisasi pencatatan penjualan dan stok pupuk pada tahap uji coba (UAT), demonstrasi skripsi, dan sebagai dasar operasional toko. Keterbatasan penelitian antara lain pengujian yang masih dominan di lingkungan lokal dan belum sepenuhnya pada server produksi dengan beban pengguna bersamaan jangka panjang.

---

## Saran

### Saran pengembangan sistem

1. Deploy ke **hosting tetap** (VPS/shared hosting/cloud) agar dapat diakses tanpa menyalakan PC pengembang.
2. **Backup database** rutin (harian/mingguan).
3. Ganti **password bawaan** setelah UAT; hanya admin yang membuat akun kasir.
4. **Audit log** untuk perubahan transaksi dan stok (opsional, untuk keamanan).
5. Notifikasi **stok rendah** atau piutang jatuh tempo (WhatsApp/email) jika diperlukan toko.

### Saran operasional toko

1. Admin mengelola akun kasir; kasir fokus pada penjualan dan piutang.
2. Rekonsiliasi stok fisik vs sistem secara berkala.
3. Unduh laporan Excel bulanan untuk arsip pemilik.
4. Pelatihan singkat alur POS dan pembayaran hutang untuk kasir baru.

### Saran dokumentasi skripsi

1. Lampirkan tangkapan layar modul utama (login, POS, hutang, laporan).
2. Jelaskan peran sistem: hanya **admin** dan **kasir**.
3. Lengkapi dengan **SUS** jika bab usability mensyaratkan data kuantitatif.
4. Cantumkan rencana hosting produksi pasca-skripsi.

---

*Lihat juga: `docs/BAB_4_5_2_BLACKBOX_SEMUA_MODUL.md`, `docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md`*
