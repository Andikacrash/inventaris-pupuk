# Bab 4.5.2 — Pengujian Black Box: Pembuatan Akun Pengguna

> **Semua modul (103 skenario):** lihat `docs/BAB_4_5_2_BLACKBOX_SEMUA_MODUL.md`

**Sistem:** Inventaris dan Penjualan Pupuk (Toko Pupuk Sawiji Tani)  
**Modul:** Pembuatan akun pengguna (`/register`)  
**Metode:** Black box testing  
**Lingkungan uji:** `http://127.0.0.1:8000` · Browser Chrome/Edge · MySQL dengan `php artisan migrate --seed`  
**Akun penguji:** Admin `admin@example.com` / `password`

## Catatan metode

Pada sistem ini, fitur **bukan registrasi publik** (pendaftaran mandiri pelanggan), melainkan **pembuatan akun karyawan oleh admin** setelah admin login. Form berisi: **Peran** (Kasir/Admin), **Nama lengkap**, **Email**, **Kata sandi**, dan **Ulangi kata sandi**. Halaman hanya dapat diakses oleh pengguna berperan **admin**.

Tabel berikut disusun mengikuti format contoh pengujian registrasi user, dengan penyesuaian field dan skenario sesuai implementasi aplikasi.

---

## A. Pengujian Pembuatan Akun Pengguna (oleh Admin)

**Tabel 4.16 Pengujian Pembuatan Akun Pengguna**

| No | Skenario Pengujian | Test Case | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|----|-------------------|-----------|----------------------|-----------------|------------|
| 1 | Semua field kosong | Peran: (kosong/tidak dipilih), Nama lengkap: (kosong), Email: (kosong), Kata sandi: (kosong), Ulangi kata sandi: (kosong) | Sistem menolak input dan menampilkan pesan validasi pada field wajib | Sesuai harapan | Valid |
| 2 | Nama diisi, email kosong | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: (kosong), Kata sandi: Aril1234!, Ulangi kata sandi: Aril1234! | Sistem menolak dan menampilkan pesan bahwa field email wajib diisi | Sesuai harapan | Valid |
| 3 | Format email tidak valid | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: khairil@, Kata sandi: Aril1234!, Ulangi kata sandi: Aril1234! | Sistem menolak dan menampilkan pesan format email tidak valid | Sesuai harapan | Valid |
| 4 | Kata sandi kurang dari 8 karakter | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: khairil@email.com, Kata sandi: 12345, Ulangi kata sandi: 12345 | Sistem menolak dan menampilkan pesan kata sandi minimal 8 karakter | Sesuai harapan | Valid |
| 5 | Konfirmasi kata sandi tidak sama | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: khairil@email.com, Kata sandi: Aril1234!, Ulangi kata sandi: Aril123 | Sistem menolak dan menampilkan pesan konfirmasi kata sandi tidak sesuai | Sesuai harapan | Valid |
| 6 | Akses tanpa hak admin | Login sebagai **kasir** (`kasir@example.com`), lalu buka halaman `/register` | Sistem menolak akses, mengarahkan ke ringkasan/dashboard, dan menampilkan pesan tidak memiliki akses ke halaman tersebut | Sesuai harapan | Valid |
| 7 | Email sudah terdaftar | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: admin@example.com (sudah ada di database), Kata sandi: Aril1234!, Ulangi kata sandi: Aril1234! | Sistem menolak dan menampilkan pesan email sudah terdaftar / sudah digunakan | Sesuai harapan | Valid |
| 8 | Nama lengkap tidak diisi (field lain valid) | Peran: Kasir, Nama lengkap: (kosong), Email: penguji.bar@email.com, Kata sandi: Aril1234!, Ulangi kata sandi: Aril1234! | Sistem menolak dan menampilkan pesan nama lengkap wajib diisi | Sesuai harapan | Valid |
| 9 | Data valid | Peran: Kasir, Nama lengkap: Khairil Hakim, Email: khairil.hakim@email.com (belum terdaftar), Kata sandi: Aril1234!, Ulangi kata sandi: Aril1234! | Sistem berhasil menyimpan akun dan menampilkan notifikasi sukses pembuatan akun | Sesuai harapan | Valid |
| 10 | Login setelah pembuatan akun | Logout admin, buka `/login`, masuk dengan Email: khairil.hakim@email.com dan Kata sandi: Aril1234! | Sistem mengizinkan login dan menampilkan halaman kasir (`/sales`) sesuai peran | Sesuai harapan | Valid |

---

## B. Narasi untuk skripsi (contoh paragraf)

Pengujian black box pada modul pembuatan akun pengguna dilakukan dengan memasukkan berbagai kombinasi data uji pada form `/register` tanpa melihat kode program. Pengujian mencakup skenario negatif (field kosong, format email salah, kata sandi tidak memenuhi syarat, email duplikat, serta akses oleh non-admin) dan skenario positif (data valid dan login akun baru). Hasil pengujian menunjukkan seluruh skenario menghasilkan keluaran sesuai spesifikasi, sehingga modul dinyatakan **valid** dan dapat diterima.

---

## C. Rekapitulasi modul registrasi / pembuatan akun

| Kategori | Jumlah kasus | Valid | Tidak valid | Persentase valid |
|----------|--------------|-------|-------------|------------------|
| Pembuatan akun pengguna | 10 | 10 | 0 | 100% |

**Kesimpulan:** Dari 10 skenario pengujian black box pada modul pembuatan akun pengguna, seluruh kasus (100%) menghasilkan keluaran sesuai yang diharapkan.

---

## D. Langkah uji (panduan singkat penguji)

1. Login sebagai admin → buka menu/footer **Buat akun** atau URL `/register`.
2. Jalankan skenario 1–5 dan 7–9 pada form yang sama; catat pesan error atau notifikasi sukses.
3. Skenario 6: logout tidak perlu; login sebagai kasir di tab lain, coba akses `/register`.
4. Skenario 10: gunakan akun yang dibuat pada skenario 9.

**Catatan kolom Hasil Pengujian:** Isi *Sesuai harapan* / *Tidak sesuai* setelah Anda menjalankan uji di browser. Contoh di atas memakai *Sesuai harapan* sebagai template skripsi.

---

## E. Perbandingan dengan contoh registrasi publik

| Aspek | Contoh skripsi referensi | Sistem inventaris pupuk |
|-------|-------------------------|-------------------------|
| Siapa yang mendaftar | User/pelanggan sendiri | Admin membuat akun karyawan |
| Field nama | Nama depan & nama belakang | Nama lengkap (satu field) |
| Nomor telepon | Ada validasi | Tidak ada pada form |
| Syarat & ketentuan | Checkbox wajib | Tidak ada; diganti uji **akses admin** |
| Setelah sukses | Registrasi + login user | Notifikasi sukses; login terpisah (uji no. 10) |
| Halaman setelah login | Beranda user | Halaman kasir `/sales` (kasir) atau modul admin |

---

*Dokumen ini melengkapi `docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md` (modul BB autentikasi).*
