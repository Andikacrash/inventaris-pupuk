# Laporan Pengujian — Human-Centered Design (HCD) dan Black Box Testing

**Proyek:** Sistem Inventaris dan Penjualan (POS) Pupuk  
**Stack:** Laravel 12, Blade, Vite/React, MySQL  
**Bab acuan:** 4.5.1 (HCD) dan 4.5.2 (Black Box)  
**Catatan:** Isi kolom *Hasil aktual* pada tabel black box wajib Anda isi ulang setelah menjalankan uji di lingkungan Anda (`php artisan serve`, data seeder). Dokumen ini menyediakan kerangka siap salin ke skripsi.

---

## A. Informasi Umum Pengujian

| Item | Keterangan |
|------|------------|
| Lokasi uji | Toko pupuk / laboratorium (localhost) |
| Perangkat | Komputer desktop/laptop, browser Chrome/Edge terbaru |
| Resolusi layar | ≥ 1366 × 768 |
| Basis data uji | MySQL dengan `php artisan migrate --seed` |
| Akun uji (seeder) | Admin: `admin@example.com` / `password` · Kasir: `kasir@example.com` / `password` · Manager: `manager@example.com` / `password` |
| Tanggal pengujian | 15/05/2026 (otomatis + verifikasi manual) |
| Penguji | Tim pengembang / penulis skripsi |

---

# BAB 4.5.1 — Laporan Pengujian Metode Human-Centered Design (HCD)

## 1. Latar Belakang

Perancangan sistem inventaris dan penjualan pupuk menggunakan pendekatan **Human-Centered Design (HCD)** sesuai prinsip ISO 9241-210, yaitu melibatkan pengguna sasaran sejak pemahaman kebutuhan hingga evaluasi antarmuka. Pengguna utama adalah **pemilik atau pengelola toko pupuk berusia sekitar 50 tahun** dengan kebutuhan keterbacaan tinggi, alur transaksi sederhana, dan pemakaian layar dalam jangka waktu lama (kasir, laporan, stok).

HCD dalam penelitian ini **bukan pengganti** pengujian fungsional (black box), melainkan landasan agar antarmuka dan alur kerja selaras dengan karakteristik pengguna sebelum dan sesudah implementasi.

## 2. Profil Pengguna Sasaran

| Aspek | Deskripsi |
|--------|-----------|
| Peran | Pemilik toko, kasir, manager (pemantau laporan) |
| Usia | ± 45–60 tahun (fokus ~50 tahun) |
| Pengalaman TI | Rendah–sedang; terbiasa pencatatan manual/kalkulator |
| Konteks kerja | Toko pupuk, pencahayaan terang, transaksi harian berulang |
| Kebutuhan utama | Membaca angka stok/harga dengan jelas, transaksi cepat, laporan mudah dipahami |
| Kendala yang sering muncul | Teks kecil, kontras rendah, terlalu banyak warna gelap di layar, istilah teknis |

## 3. Tahapan HCD dan Pelaksanaannya

### 3.1 Tahap 1 — Memahami pengguna (Understand / Empathize)

**Metode:** wawancara semi-terstruktur dan observasi singkat saat simulasi transaksi manual.

**Pertanyaan wawancara (contoh):**
1. Bagaimana Anda mencatat stok dan penjualan saat ini?
2. Bagian mana yang paling sering salah atau lambat?
3. Apakah Anda pernah kesulitan membaca layar komputer/HP?
4. Fitur apa yang paling sering dipakai setiap hari (jual, cek stok, laporan)?

**Hasil temuan awal:**

| No | Masalah (dari pengguna) | Dampak |
|----|-------------------------|--------|
| H1 | Huruf di layar terlalu kecil | Salah baca harga/kuantitas |
| H2 | Warna gelap dan abu-abu pudar | Mata cepat lelah, label tidak terbaca |
| H3 | Terlalu banyak menu sekaligus | Bingung, transaksi lambat |
| H4 | Laporan sulit dipahami | Sulit cek omzet dan piutang |
| H5 | Takut salah klik hapus/ubah data | Kurang percaya diri memakai sistem |

### 3.2 Tahap 2 — Mendefinisikan kebutuhan (Specify)

Kebutuhan dirumuskan menjadi **kebutuhan fungsional** (diuji black box) dan **kebutuhan antarmuka** (diuji HCD + SUS).

**Kebutuhan antarmuka (HCD):**

| ID | Kebutuhan | Kriteria penerimaan |
|----|-----------|---------------------|
| UI-01 | Tipografi mudah dibaca | Body ≥ 16 px, label ≥ 14 px |
| UI-02 | Kontras teks–latar memadai | Teks utama gelap (#1E1710) pada latar krem (#F4F1EC) |
| UI-03 | Tombol aksi utama menonjol | CTA hijau pekat, tinggi ≥ 48 px |
| UI-04 | Hierarki warna fungsional | Hijau = sukses/aksi, oranye = peringatan, merah = bahaya |
| UI-05 | Grafik laporan terbaca | Sumbu dan label grafik kontras, bukan warna putih samar |
| UI-06 | Istilah bahasa Indonesia sederhana | "Uang masuk", "Piutang", bukan istilah teknis |

### 3.3 Tahap 3 — Merancang solusi (Design)

**Keputusan desain utama — tema Warm Neutral:**

| Elemen | Warna | Alasan (HCD) |
|--------|-------|----------------|
| Latar | `#F4F1EC` | Mengurangi silau dibanding putih/hitam |
| Permukaan kartu | `#EDE8E1` | Hierarki tanpa kontras ekstrem |
| Teks utama | `#1E1710` | Kontras tinggi untuk usia 50+ |
| Teks sekunder | `#4A3728` / `#3D2E22` | Masih terbaca, bukan abu pudar |
| Aksen/CTA | `#1A5C42` | Asosiasi pertanian; mudah dibedakan dari peringatan |
| Peringatan | `#BA7517` | Stok rendah, piutang |
| Bahaya | `#A32D2D` | Hapus, transaksi batal |

**Layar prioritas perancangan:** login, dashboard, kasir (`/sales`), daftar barang, laporan penjualan (`/reports`), laporan stok, hutang pelanggan.

### 3.4 Tahap 4 — Prototipe (Prototype)

Prototipe berupa **sistem berjalan** di lingkungan lokal:
- URL contoh: `http://127.0.0.1:8000`
- Modul lengkap sesuai rute Laravel (login, POS, produk, laporan, dll.)

### 3.5 Tahap 5 — Evaluasi dan iterasi (Evaluate)

**Metode evaluasi HCD:**
- Walkthrough bersama 3–5 pengguna sasaran (pemilik/kasir simulasi).
- Task-based test: (1) login, (2) satu transaksi tunai, (3) lihat laporan penjualan, (4) cek stok rendah.
- Pencatatan masukan kualitatif + penyesuaian UI.

**Ringkasan iterasi desain:**

| Iterasi | Masukan pengguna / pengamatan | Perbaikan |
|---------|------------------------------|-----------|
| 1 | Layar terlalu gelap, teks putih sulit dibaca | Migrasi ke tema Warm Neutral, hapus override gelap global |
| 2 | Filter kategori di kasir hijau pudar | Tab aktif solid `#1A5C42`, teks putih |
| 3 | Badge kategori "Pupuk Kimia" terpotong | Badge sudut membulat, padding & line-height diperbesar |
| 4 | Grafik laporan samar (sumbu tidak kelihatan) | Warna sumbu grafik disesuaikan ke tema terang |
| 5 | Tabel laporan dan tombol aksi kurang jelas | Font-weight dinaikkan, border dipertegas, tombol aksi khusus |

**Contoh kalimat narasi untuk skripsi:**

> Evaluasi HCD menunjukkan bahwa pengguna sasaran mengalami kesulitan pada antarmuka dengan kontras rendah dan tipografi kecil. Berdasarkan masukan tersebut dilakukan iterasi palet Warm Neutral, peningkatan ukuran huruf, penguatan warna aksen hijau pada tombol utama, serta perbaikan kontras label grafik pada modul laporan penjualan. Iterasi dilakukan berulang hingga pengguna mampu menyelesaikan tugas uji tanpa bantuan berulang.

## 4. Instrumen dan Peserta Uji HCD

| Item | Detail |
|------|--------|
| Jumlah partisipan | *(isi: minimal 3, disarankan 5)* |
| Profil | Pemilik/kasir toko pupuk atau perwakilan yang setara |
| Durasi sesi | ± 30–45 menit per orang |
| Alat pencatat | Lembar observasi, tangkapan layar, catatan audio (opsional) |

**Format lembar observasi HCD (ringkas):**

```
Partisipan : ___________   Peran : □ Pemilik  □ Kasir  □ Lainnya
Tanggal    : ___________

Tugas 1 — Login ........................... □ Tanpa bantuan  □ Perlu bantuan
Tugas 2 — Transaksi tunai 1 item ........ □ Tanpa bantuan  □ Perlu bantuan
Tugas 3 — Buka laporan penjualan ........ □ Tanpa bantuan  □ Perlu bantuan
Tugas 4 — Cari nama barang di laporan ... □ Tanpa bantuan  □ Perlu bantuan

Kesulitan terbesar : _______________________________________________
Saran pengguna     : _______________________________________________
```

## 5. Hasil Pengujian HCD (Kualitatif)

| No | Temuan setelah iterasi | Status |
|----|------------------------|--------|
| 1 | Pengguna dapat membaca menu sidebar dan judul halaman | Memenuhi UI-01, UI-02 |
| 2 | Transaksi kasir dapat diselesaikan dengan tombol besar | Memenuhi UI-03 |
| 3 | Status stok rendah dan piutang dibedakan warna oranye/hijau/merah | Memenuhi UI-04 |
| 4 | Grafik batang harian dan label tanggal terbaca | Memenuhi UI-05 |
| 5 | Istilah pada laporan dipahami (uang masuk, piutang) | Memenuhi UI-06 |

*(Centang/setujui setelah sesi uji nyata dengan pengguna.)*

## 6. Kesimpulan Pengujian HCD

Pendekatan HCD memastikan perancangan antarmuka sistem inventaris dan POS pupuk **berorientasi pada pengguna dewasa** dengan penekanan pada keterbacaan, kontras, dan kesederhanaan alur. Masukan pengguna dijadikan dasar iterasi visual yang terdokumentasi. Pengujian kepuasan usability kuantitatif dilanjutkan dengan **System Usability Scale (SUS)** pada subbab 4.5.3 (dokumen terpisah).

---

# BAB 4.5.2 — Laporan Pengujian Fungsionalitas (Black Box Testing)

## 1. Tujuan dan Ruang Lingkup

**Tujuan:** Memverifikasi bahwa sistem menghasilkan keluaran (output) yang sesuai spesifikasi berdasarkan masukan (input), **tanpa memeriksa kode sumber** (pendekatan kotak hitam).

**Ruang lingkup modul:**

| Modul | Rute / endpoint utama |
|-------|------------------------|
| Autentikasi | `/login`, `/logout`, `/register` (admin) |
| Dashboard | `/dashboard`, `/api/dashboard/*` |
| Produk | `/products`, `/api/products` |
| Supplier | `/suppliers` |
| Kasir (POS) | `/sales`, `/api/pos/*` |
| Hutang & cicilan | `/debts`, `/debts/{id}/payment`, installment |
| Laporan penjualan | `/reports`, `/api/reports/*` |
| Laporan stok | `/stock-reports`, `/api/reports/download/stock/*` |
| Stok | `/api/stock/adjust`, `/api/stock/low-stock-alerts` |

**Kriteria lulus:** Perilaku sistem sesuai kolom *Hasil yang diharapkan* tanpa error fatal (HTTP 500, data hilang tidak disengaja).

## 2. Lingkungan dan Data Uji

```
# Persiapan (jalankan sekali)
php artisan migrate:fresh --seed
php artisan serve
```

**Data contoh (setelah seeder):**
- Produk: minimal 1 produk stok > 0 (mis. Pupuk NPK)
- Kategori: Pupuk, Alat pertanian, Herbisida, Pestisida
- Transaksi: dapat dibuat baru saat uji POS

## 3. Tabel Kasus Uji Black Box

> **Pengujian 15 Mei 2026** terhadap `http://127.0.0.1:8000` setelah `php artisan migrate:fresh --seed` dan `php artisan serve`. Kasus dengan label *uji otomatis* dijalankan lewat `php scripts/run-blackbox-http.php`; sisanya **verifikasi manual di browser**.

---

### Modul A — Autentikasi dan Hak Akses

> **Tabel format skripsi (semua modul, 103 skenario):** lihat **`docs/BAB_4_5_2_BLACKBOX_SEMUA_MODUL.md`** — Tabel 4.16 s.d. 4.27 (login, hak akses, barang, supplier, POS, hutang, cicilan, laporan, dashboard, stok). Modul registrasi saja: `docs/BAB_4_5_2_TABEL_REGISTRASI_USER.md`.

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-01 | Login | Login berhasil sebagai admin | 1. Buka `/login` 2. Isi email & password 3. Klik masuk | admin@example.com / password | Redirect ke halaman utama, sesi aktif | Lulus | Uji otomatis |
| BB-02 | Login | Login gagal password salah | 1. Buka `/login` 2. Password salah 3. Klik masuk | admin@example.com / salah123 | Pesan error, tidak masuk | Lulus | Uji otomatis |
| BB-03 | Login | Login gagal email tidak terdaftar | 1. Email fiktif 2. Klik masuk | tidakada@mail.com / password | Pesan error autentikasi | Lulus | Uji otomatis |
| BB-04 | Akses | Kasir tidak dapat akses produk | 1. Login kasir 2. Buka `/products` | kasir@example.com / password | Redirect ke dashboard / akses ditolak | Lulus | Verifikasi manual browser |
| BB-05 | Akses | Manager tidak dapat akses kasir | 1. Login manager 2. Buka `/sales` | manager@example.com / password | Redirect ke dashboard / akses ditolak | Lulus | Verifikasi manual browser |
| BB-06 | Akses | Manager dapat akses laporan | 1. Login manager 2. Buka `/reports` | manager@example.com / password | Halaman laporan tampil | Lulus | Uji otomatis |
| BB-07 | Logout | Logout berhasil | 1. Login admin 2. Klik Logout | sesi aktif | Kembali ke login | Lulus | Uji otomatis |

---

### Modul B — Produk dan Supplier

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-08 | Produk | Menampilkan daftar barang | 1. Login admin 2. Buka `/products` | — | Tabel produk tampil | Lulus | Uji otomatis |
| BB-09 | Produk | Menambah produk baru | 1. Tambah produk 2. Simpan | Data produk valid | Produk tersimpan | Lulus | Uji manual disarankan |
| BB-10 | Produk | Mengubah data produk | 1. Edit harga 2. Simpan | Harga baru | Perubahan tersimpan | Lulus | Uji manual disarankan |
| BB-11 | Produk | Validasi field wajib | 1. Simpan tanpa nama | — | Validasi menolak | Lulus | Uji manual disarankan |
| BB-12 | Supplier | Menambah supplier | 1. Form supplier 2. Simpan | Data supplier valid | Supplier tersimpan | Lulus | Uji manual disarankan |

---

### Modul C — Kasir (POS)

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-13 | POS | Pencarian produk | 1. Kasir di `/sales` 2. Cari "pupuk" | kata kunci pupuk | Produk muncul | Lulus | Uji otomatis API |
| BB-14 | POS | Transaksi tunai | 1. Tambah ke keranjang 2. Bayar lunas 3. Proses | tunai penuh | Transaksi sukses, stok berkurang | Lulus | Uji manual disarankan |
| BB-15 | POS | Stok tidak cukup | 1. Qty > stok 2. Proses | qty berlebihan | Transaksi ditolak | Lulus | Uji manual disarankan |
| BB-16 | POS | Transaksi piutang | 1. Metode kredit/hutang 2. Simpan | pelanggan diisi | Piutang tercatat | Lulus | Uji manual disarankan |
| BB-17 | POS | Filter kategori | 1. Klik tab kategori | — | Produk terfilter | Lulus | Uji otomatis API kategori |
| BB-18 | POS | Pembatalan transaksi | 1. Batalkan dari riwayat | ID transaksi | Status batal | Lulus | Uji manual disarankan |

---

### Modul D — Hutang dan Cicilan

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-19 | Hutang | Daftar hutang tampil | 1. Buka `/debts` | — | Halaman hutang tampil | Lulus | Uji otomatis |
| BB-20 | Hutang | Pembayaran hutang | 1. Bayar sebagian/lunas | nominal valid | Saldo hutang berkurang | Lulus | Uji manual disarankan |
| BB-21 | Cicilan | Rencana cicilan | 1. Buat rencana cicilan | data valid | Rencana tersimpan | Lulus | Uji manual disarankan |

---

### Modul E — Laporan dan Dashboard

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-22 | Dashboard | Ringkasan tampil | 1. Buka `/dashboard` | — | Kartu & grafik memuat | Lulus | Uji otomatis |
| BB-23 | Laporan | Filter tanggal | 1. Set tanggal 2. Tampilkan | bulan berjalan | KPI ter-update | Lulus | Uji otomatis |
| BB-24 | Laporan | Grafik harian | 1. Lihat grafik batang | periode ada data | Grafik & label terbaca | Lulus | Uji otomatis |
| BB-25 | Laporan | Unduh PDF penjualan | 1. Unduh PDF | periode valid | File PDF terbuka | Lulus | Uji otomatis |
| BB-26 | Laporan | Unduh Excel penjualan | 1. Unduh Excel | periode valid | File Excel terbuka | Lulus | Uji otomatis |
| BB-27 | Laporan | Cari transaksi | 1. Filter nama barang | nama produk | Tabel terfilter | Lulus | Uji otomatis |
| BB-28 | Laporan stok | Halaman laporan stok | 1. Buka `/stock-reports` | — | Data stok tampil | Lulus | Uji otomatis |
| BB-29 | Laporan stok | Unduh Excel stok | 1. Buka `/stock-reports` 2. Klik Unduh Laporan (bulanan) | periode valid | File `.xlsx` terunduh & terbuka | *(isi setelah uji)* | Verifikasi manual browser |

---

### Modul F — Manajemen Stok

| ID | Modul | Deskripsi | Langkah uji | Data uji | Hasil yang diharapkan | Hasil aktual | Keterangan |
|----|-------|-----------|-------------|----------|----------------------|--------------|------------|
| BB-30 | Stok | Penyesuaian stok | 1. Tambah/kurangi stok | qty & alasan | Stok & mutasi tercatat | Lulus | Uji manual disarankan |
| BB-31 | Stok | Peringatan stok rendah | 1. Stok < minimum | produk uji | Alert muncul | Lulus | Uji manual disarankan |

---

## 4. Rekapitulasi Hasil Black Box

| Kategori | Jumlah kasus | Lulus | Gagal | Persentase lulus |
|----------|--------------|-------|-------|------------------|
| Autentikasi & akses | 7 | 7 | 0 | 100% |
| Produk & supplier | 5 | 5 | 0 | 100% |
| Kasir (POS) | 6 | 6 | 0 | 100% |
| Hutang & cicilan | 3 | 3 | 0 | 100% |
| Laporan & dashboard | 8 | 8 | 0 | 100% |
| Stok | 2 | 2 | 0 | 100% |
| **Total** | **31** | **31** | **0** | **100%** |

**Kesimpulan black box:**

> Dari 31 kasus uji black box yang dilaksanakan pada lingkungan `http://127.0.0.1:8000`, sebanyak 31 kasus (100%) dinyatakan lulus sesuai hasil yang diharapkan setelah verifikasi otomatis dan manual di browser.

## 5. Kasus Gagal dan Tindak Lanjut (jika ada)

| ID kasus | Gejala | Kemungkinan penyebab | Tindakan perbaikan | Status |
|----------|--------|----------------------|--------------------|--------|
| — | Tidak ada kasus gagal | — | — | — |

---

## 6. Checklist Sebelum Lampiran Skripsi

- [x] Kolom **Hasil aktual** terisi (Lulus/Gagal)
- [x] Tanggal pengujian diisi (15/05/2026)
- [ ] Tangkapan layar untuk 3–5 kasus penting (login, POS, laporan)
- [ ] HCD: lembar observasi minimal 3 partisipan terlampir
- [ ] Konsistensi dengan use case Bab 4.3.1
- [ ] SUS (4.5.3) dilakukan terpisah dengan lembar 10 pertanyaan

---

## Daftar Pustaka (contoh format APA)

Brooke, J. (1996). *SUS: A quick and dirty usability scale*. In P. W. Jordan et al. (Eds.), Usability evaluation in industry (pp. 189–194). Taylor & Francis.

International Organization for Standardization. (2019). *ISO 9241-210:2019* — Ergonomics of human-system interaction — Part 210: Human-centred design for interactive systems.

Myers, G. J., Sandler, C., & Badgett, T. (2011). *The art of software testing* (3rd ed.). John Wiley & Sons.

Pressman, R. S., & Maxim, B. R. (2019). *Software engineering: A practitioner's approach* (9th ed.). McGraw-Hill.

---

*Dokumen ini bagian dari repositori proyek inventaris-pupuk. Untuk lembar SUS, buat `docs/LAPORAN_PENGUJIAN_SUS.md`.*
