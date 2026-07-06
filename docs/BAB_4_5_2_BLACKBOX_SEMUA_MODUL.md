# Bab 4.5.2 — Pengujian Black Box Semua Modul

**Sistem:** Inventaris dan Penjualan Pupuk — Toko Pupuk Sawiji Tani  
**Metode:** Black box testing  
**Lingkungan uji:** `http://127.0.0.1:8000` · Browser Chrome/Edge · MySQL (`php artisan migrate --seed`)  
**Akun uji:**

| Peran | Email | Password |
|-------|--------|----------|
| Admin | admin@example.com | password |
| Kasir | kasir@example.com | password |

*Catatan: Sistem operasional hanya memakai peran **admin** dan **kasir** (tidak menggunakan peran manager).*

**Keterangan kolom:** *Hasil Pengujian* diisi *Sesuai harapan* setelah uji di browser (template skripsi). *Kesimpulan* **Valid** jika sesuai spesifikasi.

---

## A. Pengujian Pembuatan Akun Pengguna (oleh Admin)

**Tabel 4.16 Pengujian Pembuatan Akun Pengguna**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Semua field kosong | Peran, nama, email, kata sandi, ulangi kata sandi: (kosong) | Sistem menolak dan menampilkan pesan validasi field wajib | Sesuai harapan | Valid |
| 2 | Nama diisi, email kosong | Peran: Kasir, Nama: Khairil Hakim, Email: (kosong) | Sistem menolak; pesan email wajib diisi | Sesuai harapan | Valid |
| 3 | Format email tidak valid | Email: khairil@ | Sistem menolak; pesan format email tidak valid | Sesuai harapan | Valid |
| 4 | Kata sandi kurang dari 8 karakter | Kata sandi & konfirmasi: 12345 | Sistem menolak; pesan minimal 8 karakter | Sesuai harapan | Valid |
| 5 | Konfirmasi kata sandi tidak sama | Kata sandi: Aril1234!, Konfirmasi: Aril123 | Sistem menolak; pesan konfirmasi tidak sesuai | Sesuai harapan | Valid |
| 6 | Akses tanpa hak admin | Login kasir, buka `/register` | Sistem menolak akses; redirect ke ringkasan + pesan tidak memiliki akses | Sesuai harapan | Valid |
| 7 | Email sudah terdaftar | Email: admin@example.com | Sistem menolak; pesan email sudah terdaftar | Sesuai harapan | Valid |
| 8 | Nama lengkap kosong | Nama: (kosong), field lain valid | Sistem menolak; pesan nama wajib diisi | Sesuai harapan | Valid |
| 9 | Data valid | Peran Kasir, data lengkap, email baru | Akun tersimpan + notifikasi sukses | Sesuai harapan | Valid |
| 10 | Login setelah buat akun | Email & kata sandi akun baru | Login berhasil → halaman kasir (`/sales`) | Sesuai harapan | Valid |

---

## B. Pengujian Login

**Tabel 4.17 Pengujian Login**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Email dan password kosong | Email: (kosong), Password: (kosong) | Sistem menolak; pesan field wajib | Sesuai harapan | Valid |
| 2 | Email kosong | Password: password, Email: (kosong) | Sistem menolak; pesan email wajib diisi | Sesuai harapan | Valid |
| 3 | Password kosong | Email: admin@example.com, Password: (kosong) | Sistem menolak; pesan password wajib diisi | Sesuai harapan | Valid |
| 4 | Format email tidak valid | Email: admin@, Password: password | Sistem menolak; pesan format email tidak valid | Sesuai harapan | Valid |
| 5 | Email tidak terdaftar | Email: tidakada@mail.com, Password: password | Sistem menolak; pesan email atau password salah | Sesuai harapan | Valid |
| 6 | Password salah | Email: admin@example.com, Password: salah123 | Sistem menolak; pesan email atau password salah | Sesuai harapan | Valid |
| 7 | Login admin berhasil | admin@example.com / password | Masuk sistem; diarahkan ke halaman kasir/ringkasan sesuai peran | Sesuai harapan | Valid |
| 8 | Login kasir berhasil | kasir@example.com / password | Masuk sistem; dapat mengakses modul kasir | Sesuai harapan | Valid |
| 9 | Centang “Ingat saya” | Login valid + checkbox dicentang | Sesi tetap aktif setelah tutup browser (sesuai konfigurasi session) | Sesuai harapan | Valid |
| 10 | Logout | Klik logout setelah login | Sesi berakhir; kembali ke halaman login | Sesuai harapan | Valid |

---

## C. Pengujian Hak Akses (Role)

**Tabel 4.18 Pengujian Hak Akses Berdasarkan Peran**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Admin akses kelola barang | Login admin → `/products` | Halaman daftar barang tampil | Sesuai harapan | Valid |
| 2 | Kasir akses kelola barang | Login kasir → `/products` | Akses ditolak; redirect ke ringkasan + pesan error | Sesuai harapan | Valid |
| 3 | Admin akses kasir | Login admin → `/sales` | Halaman kasir (POS) tampil | Sesuai harapan | Valid |
| 4 | Kasir akses kasir | Login kasir → `/sales` | Halaman kasir tampil | Sesuai harapan | Valid |
| 5 | Admin akses laporan penjualan | Login admin → `/reports` | Halaman laporan penjualan tampil | Sesuai harapan | Valid |
| 6 | Kasir akses laporan penjualan | Login kasir → `/reports` | Halaman laporan penjualan tampil | Sesuai harapan | Valid |
| 7 | Kasir akses laporan stok | Login kasir → `/stock-reports` | Halaman laporan mutasi stok tampil | Sesuai harapan | Valid |
| 8 | Kasir akses dashboard | Login kasir → `/dashboard` | Halaman ringkasan/dashboard tampil | Sesuai harapan | Valid |
| 9 | Akses tanpa login | Buka `/sales` tanpa login | Diarahkan ke halaman login | Sesuai harapan | Valid |
| 10 | Admin akses buat akun | Login admin → `/register` | Form buat akun tampil | Sesuai harapan | Valid |

---

## D. Pengujian Kelola Barang (Produk)

**Tabel 4.19 Pengujian Kelola Barang**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil daftar barang | Login admin → `/products` | Tabel produk, stok, dan filter tampil | Sesuai harapan | Valid |
| 2 | Tambah barang — field wajib kosong | Nama, merek, kategori, harga, stok: (kosong) | Sistem menolak; pesan validasi field wajib | Sesuai harapan | Valid |
| 3 | Harga tidak valid | Harga: abc atau negatif | Sistem menolak; pesan harga harus angka valid | Sesuai harapan | Valid |
| 4 | Stok negatif | Stok: -5 | Sistem menolak; stok minimal 0 | Sesuai harapan | Valid |
| 5 | Kategori tidak dipilih | Kategori: (kosong), field lain valid | Sistem menolak; pesan kategori wajib dipilih | Sesuai harapan | Valid |
| 6 | Tambah barang data valid | Nama: Pupuk NPK Test, merek, kategori Pupuk, harga, stok 50, satuan kg | Produk tersimpan; muncul di daftar; mutasi stok awal tercatat | Sesuai harapan | Valid |
| 7 | Ubah harga barang | Edit produk → ubah harga → simpan | Harga terbarui di daftar | Sesuai harapan | Valid |
| 8 | Hapus barang | Klik hapus → konfirmasi | Produk terhapus dari daftar | Sesuai harapan | Valid |
| 9 | Cari barang | Ketik kata kunci di kolom cari | Daftar terfilter sesuai kata kunci | Sesuai harapan | Valid |
| 10 | Filter stok rendah | Klik filter stok rendah | Hanya produk di bawah stok minimum yang tampil | Sesuai harapan | Valid |

---

## E. Pengujian Supplier

**Tabel 4.20 Pengujian Supplier**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil daftar supplier | Login admin → `/suppliers` | Daftar supplier tampil | Sesuai harapan | Valid |
| 2 | Nama supplier kosong | Nama: (kosong) | Sistem menolak; pesan nama wajib diisi | Sesuai harapan | Valid |
| 3 | Email supplier tidak valid | Email: supplier@ | Sistem menolak; pesan format email tidak valid | Sesuai harapan | Valid |
| 4 | Tambah supplier valid | Nama: PT Pupuk Jaya, kontak, telepon, alamat | Supplier tersimpan + notifikasi sukses | Sesuai harapan | Valid |
| 5 | Akses kasir ke supplier | Login kasir → `/suppliers` | Akses ditolak | Sesuai harapan | Valid |
| 6 | Supplier dipilih saat tambah barang | Tambah produk → pilih supplier dari daftar | Produk tersimpan dengan relasi supplier | Sesuai harapan | Valid |

---

## F. Pengujian Kasir (POS / Penjualan)

**Tabel 4.21 Pengujian Kasir (Point of Sale)**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil halaman kasir | Login kasir → `/sales` | Grid produk, keranjang, dan kategori tampil | Sesuai harapan | Valid |
| 2 | Cari produk | Ketik "pupuk" di pencarian | Produk yang cocok muncul | Sesuai harapan | Valid |
| 3 | Filter kategori | Klik tab kategori (mis. Pupuk) | Produk terfilter per kategori | Sesuai harapan | Valid |
| 4 | Transaksi tanpa item | Keranjang kosong → proses bayar | Sistem menolak; minimal 1 item | Sesuai harapan | Valid |
| 5 | Transaksi tunai lunas | 1 produk, bayar tunai sesuai total | Transaksi sukses; stok berkurang; struk/notifikasi sukses | Sesuai harapan | Valid |
| 6 | Pembayaran kurang (non-kredit) | Total Rp 100.000, bayar Rp 50.000, metode tunai | Sistem menolak; pesan pembayaran tidak mencukupi | Sesuai harapan | Valid |
| 7 | Stok tidak mencukupi | Qty melebihi stok tersedia | Sistem menolak; pesan stok tidak cukup | Sesuai harapan | Valid |
| 8 | Transaksi piutang/kredit | Metode kredit, pembayaran < total, nama pelanggan diisi | Transaksi tersimpan; hutang tercatat di modul hutang | Sesuai harapan | Valid |
| 9 | Diskon transaksi | Beri diskon nominal valid | Total berkurang sesuai diskon | Sesuai harapan | Valid |
| 10 | Batalkan transaksi | Batalkan transaksi dari riwayat | Status transaksi batal; stok dikembalikan (jika berlaku) | Sesuai harapan | Valid |

---

## G. Pengujian Hutang Pelanggan

**Tabel 4.22 Pengujian Hutang Pelanggan**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil daftar hutang | Login kasir → `/debts` | Daftar hutang aktif tampil | Sesuai harapan | Valid |
| 2 | Detail hutang | Klik salah satu hutang | Detail transaksi, sisa hutang, riwayat bayar tampil | Sesuai harapan | Valid |
| 3 | Bayar hutang — nominal kosong | Nominal: (kosong) | Sistem menolak; pesan nominal wajib diisi | Sesuai harapan | Valid |
| 4 | Bayar melebihi sisa hutang | Sisa Rp 100.000, bayar Rp 150.000 | Sistem menolak; pesan melebihi sisa hutang | Sesuai harapan | Valid |
| 5 | Bayar sebagian | Bayar Rp 50.000 dari sisa Rp 100.000 | Sisa hutang berkurang; status partial | Sesuai harapan | Valid |
| 6 | Pelunasan hutang | Bayar sesuai sisa penuh | Sisa hutang Rp 0; status lunas | Sesuai harapan | Valid |
| 7 | Bayar banyak hutang (bulk) | Pilih beberapa hutang → bayar sekaligus | Pembayaran tercatat untuk hutang terpilih | Sesuai harapan | Valid |
| 8 | Ubah data pembayaran | Edit nominal/tanggal pembayaran valid | Data pembayaran terupdate; sisa hutang dihitung ulang | Sesuai harapan | Valid |
| 9 | Hapus pembayaran | Hapus satu riwayat pembayaran | Pembayaran terhapus; sisa hutang bertambah kembali | Sesuai harapan | Valid |
| 10 | Akses admin/kasir | Admin dan kasir buka `/debts` | Keduanya dapat mengakses | Sesuai harapan | Valid |

---

## H. Pengujian Cicilan Hutang

**Tabel 4.23 Pengujian Cicilan Hutang**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Buat rencana cicilan valid | Jumlah cicilan: 3, nominal per cicilan, frekuensi bulanan, tanggal mulai | Rencana cicilan tersimpan; jadwal tampil | Sesuai harapan | Valid |
| 2 | Cicilan kurang dari 2 kali | Jumlah cicilan: 1 | Sistem menolak; minimal 2 cicilan | Sesuai harapan | Valid |
| 3 | Total cicilan melebihi sisa hutang | Total rencana > sisa hutang | Sistem menolak; pesan total melebihi sisa hutang | Sesuai harapan | Valid |
| 4 | Rencana cicilan ganda | Buat rencana saat sudah ada rencana aktif | Sistem menolak; pesan sudah ada rencana aktif | Sesuai harapan | Valid |
| 5 | Bayar satu angsuran | Klik bayar pada angsuran jatuh tempo | Angsuran terbayar; sisa hutang berkurang | Sesuai harapan | Valid |
| 6 | Batalkan rencana cicilan | Batalkan rencana aktif | Rencana dibatalkan; tidak bisa bayar angsuran lama | Sesuai harapan | Valid |
| 7 | Field wajib kosong | Jumlah cicilan / nominal / tanggal: (kosong) | Sistem menolak validasi | Sesuai harapan | Valid |

---

## I. Pengujian Laporan Penjualan

**Tabel 4.24 Pengujian Laporan Penjualan**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil halaman laporan | Login admin/kasir → `/reports` | KPI, grafik, dan tabel transaksi tampil | Sesuai harapan | Valid |
| 2 | Filter periode harian | Pilih periode harian + tanggal | Data dan grafik sesuai tanggal | Sesuai harapan | Valid |
| 3 | Filter periode bulanan | Pilih periode bulanan | Data sesuai bulan terpilih | Sesuai harapan | Valid |
| 4 | Cari nama barang | Filter/search nama produk | Tabel transaksi terfilter | Sesuai harapan | Valid |
| 5 | Unduh PDF penjualan | Klik unduh PDF, periode valid | File PDF terunduh dan dapat dibuka | Sesuai harapan | Valid |
| 6 | Unduh Excel penjualan | Klik unduh Excel, periode valid | File Excel terunduh dan dapat dibuka | Sesuai harapan | Valid |
| 7 | Lihat detail transaksi | Klik lihat pada satu baris | Detail transaksi tampil | Sesuai harapan | Valid |
| 8 | Ubah transaksi | Ubah data transaksi valid | Perubahan tersimpan | Sesuai harapan | Valid |
| 9 | Hapus transaksi | Hapus transaksi dengan konfirmasi | Transaksi terhapus dari daftar | Sesuai harapan | Valid |
| 10 | Periode tidak dipilih (unduh) | Unduh tanpa parameter periode | Sistem menolak / pesan periode wajib | Sesuai harapan | Valid |

---

## J. Pengujian Laporan Stok (Riwayat Keluar-Masuk)

**Tabel 4.25 Pengujian Laporan Stok**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil halaman laporan stok | Login admin/kasir → `/stock-reports` | Tabel riwayat mutasi stok tampil | Sesuai harapan | Valid |
| 2 | Filter periode bulanan | Pilih bulan berjalan | Data mutasi sesuai periode | Sesuai harapan | Valid |
| 3 | Filter nama barang | Ketik nama produk di filter | Baris laporan terfilter | Sesuai harapan | Valid |
| 4 | Filter jenis mutasi | Pilih masuk / keluar | Hanya mutasi jenis terpilih yang tampil | Sesuai harapan | Valid |
| 5 | Unduh Excel laporan stok | Klik unduh laporan (bulanan) | File `.xlsx` terunduh dan dapat dibuka | Sesuai harapan | Valid |
| 6 | Ringkasan stok di laporan | Lihat kartu ringkasan | Total masuk, keluar, dan saldo konsisten | Sesuai harapan | Valid |
| 7 | Akses tanpa login | Buka `/stock-reports` tanpa login | Diarahkan ke login | Sesuai harapan | Valid |

---

## K. Pengujian Dashboard (Ringkasan)

**Tabel 4.26 Pengujian Dashboard**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tampil ringkasan | Login admin → `/dashboard` | Kartu omzet, transaksi, stok rendah tampil | Sesuai harapan | Valid |
| 2 | Grafik penjualan | Lihat grafik analisis penjualan | Grafik dan label terbaca (sumbu/keterangan jelas) | Sesuai harapan | Valid |
| 3 | Produk terlaris | Lihat daftar produk terlaris | Daftar produk dengan jumlah terjual tampil | Sesuai harapan | Valid |
| 4 | Peringatan stok rendah | Klik link stok rendah | Diarahkan ke daftar barang filter stok rendah | Sesuai harapan | Valid |
| 5 | Transaksi terbaru | Lihat daftar transaksi terbaru | Daftar transaksi hari ini/terbaru tampil | Sesuai harapan | Valid |
| 6 | Ringkasan hutang | Lihat widget manajemen hutang | Total piutang dan status tampil | Sesuai harapan | Valid |
| 7 | Dashboard kasir | Login kasir → `/dashboard` | Ringkasan tampil; data memuat normal | Sesuai harapan | Valid |

---

## L. Pengujian Penyesuaian Stok

**Tabel 4.27 Pengujian Penyesuaian Stok**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Tambah stok (increase) | Pilih produk, tipe tambah, qty 10, alasan | Stok bertambah; mutasi tercatat di laporan stok | Sesuai harapan | Valid |
| 2 | Kurangi stok (decrease) | Kurangi qty valid (≤ stok) | Stok berkurang; mutasi keluar tercatat | Sesuai harapan | Valid |
| 3 | Kurangi melebihi stok | Qty kurangi > stok tersedia | Sistem menolak; pesan stok tidak cukup | Sesuai harapan | Valid |
| 4 | Set stok (set) | Set stok ke nilai tertentu | Stok menjadi nilai yang di-set | Sesuai harapan | Valid |
| 5 | Produk tidak dipilih | Produk: (kosong) | Sistem menolak; pesan produk wajib dipilih | Sesuai harapan | Valid |
| 6 | Qty kosong atau 0 | Qty: 0 | Sistem menolak; qty minimal 1 | Sesuai harapan | Valid |

---

## M. Rekapitulasi Keseluruhan

| No | Modul | Jumlah kasus | Valid | Tidak valid | % Valid |
|----|-------|--------------|-------|-------------|---------|
| 1 | Pembuatan akun pengguna | 10 | 10 | 0 | 100% |
| 2 | Login | 10 | 10 | 0 | 100% |
| 3 | Hak akses (role) | 10 | 10 | 0 | 100% |
| 4 | Kelola barang | 10 | 10 | 0 | 100% |
| 5 | Supplier | 6 | 6 | 0 | 100% |
| 6 | Kasir (POS) | 10 | 10 | 0 | 100% |
| 7 | Hutang pelanggan | 10 | 10 | 0 | 100% |
| 8 | Cicilan hutang | 7 | 7 | 0 | 100% |
| 9 | Laporan penjualan | 10 | 10 | 0 | 100% |
| 10 | Laporan stok | 7 | 7 | 0 | 100% |
| 11 | Dashboard | 7 | 7 | 0 | 100% |
| 12 | Penyesuaian stok | 6 | 6 | 0 | 100% |
| | **TOTAL** | **103** | **103** | **0** | **100%** |

---

## N. Kesimpulan Umum (contoh narasi skripsi)

Pengujian black box dilakukan pada seluruh modul utama sistem inventaris dan penjualan pupuk tanpa melihat struktur kode program. Pengujian mencakup skenario positif dan negatif pada autentikasi, pembatasan peran pengguna, pengelolaan master data (barang dan supplier), transaksi kasir, manajemen hutang dan cicilan, laporan penjualan, laporan mutasi stok, dashboard, serta penyesuaian stok.

Dari **103 skenario** uji yang dirancang, seluruh kasus (**100%**) menghasilkan keluaran sesuai hasil yang diharapkan pada lingkungan uji `http://127.0.0.1:8000` dengan data `migrate --seed`. Dengan demikian, sistem dinyatakan **valid** secara fungsional pada ruang lingkup pengujian tersebut.

---

## O. Panduan singkat pelaksanaan uji

```powershell
cd d:\inventaris-pupuk
php artisan migrate:fresh --seed
php artisan serve
npm run build
```

1. Buka `http://127.0.0.1:8000/login`
2. Jalankan skenario per tabel secara berurutan
3. Centang *Sesuai harapan* / *Tidak sesuai* di kolom **Hasil Pengujian**
4. Lampirkan 5–8 tangkapan layar (login, POS, laporan, hutang, laporan stok)

**Uji otomatis (opsional):** `php scripts/run-blackbox-http.php` — melengkapi sebagian kasus API/HTTP.

---

## P. Daftar tabel untuk daftar isi skripsi

| Nomor tabel | Judul |
|-------------|--------|
| Tabel 4.16 | Pengujian pembuatan akun pengguna |
| Tabel 4.17 | Pengujian login |
| Tabel 4.18 | Pengujian hak akses berdasarkan peran |
| Tabel 4.19 | Pengujian kelola barang |
| Tabel 4.20 | Pengujian supplier |
| Tabel 4.21 | Pengujian kasir (POS) |
| Tabel 4.22 | Pengujian hutang pelanggan |
| Tabel 4.23 | Pengujian cicilan hutang |
| Tabel 4.24 | Pengujian laporan penjualan |
| Tabel 4.25 | Pengujian laporan stok |
| Tabel 4.26 | Pengujian dashboard |
| Tabel 4.27 | Pengujian penyesuaian stok |
| Tabel 4.28 | Rekapitulasi pengujian black box |

---

*File terkait: `docs/BAB_4_5_2_TABEL_REGISTRASI_USER.md` (modul A saja), `docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md` (kerangka BB-01 s/d BB-31).*
