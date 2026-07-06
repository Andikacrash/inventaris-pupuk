# Hasil Wawancara Pengguna — Perancangan UI Sistem Inventaris & Penjualan Pupuk

**Judul penelitian:** Sistem Informasi Inventaris dan Penjualan Pupuk (Toko Pupuk Sawiji Tani)  
**Metode:** Wawancara semi-terstruktur + uji tampilan antarmuka (3 tahap desain)  
**Periode:** Maret – Mei 2026  
**Lokasi:** Toko pupuk / ruang uji di Kab. Sungai Penuh, Jambi (simulasi operasional)

---

## 1. Tujuan Wawancara

1. Memahami cara kerja pencatatan stok dan penjualan saat ini (manual/semi-digital).  
2. Menggali kendala pengguna dewasa (±50 tahun) saat memakai layar komputer.  
3. Mengumpulkan masukan terhadap **tiga tahap tampilan** sistem:
   - **Versi A** — tampilan polos (dominan putih, minim hierarki visual);
   - **Versi B** — tampilan gelap (latar hijau/tua gelap, teks terang);
   - **Versi C** — tampilan saat ini (**Warm Neutral**: krem, teks gelap, aksen hijau).

---

## 2. Profil Informan

| Kode | Nama (fiktif) | Usia | Pendidikan | Peran | Pengalaman komputer |
|------|---------------|------|------------|-------|---------------------|
| P1 | Bapak Harman | 52 | SMA | Pemilik toko | Jarang; biasa HP untuk WhatsApp |
| P2 | Ibu Siti | 48 | SMP | Kasir | Sedang; pernah pakai aplikasi kasir sederhana |
| P3 | Bapak Joko | 55 | SMA | Admin / pengelola stok | Rendah; lebih nyaman buku catatan |

*Keterangan: Nama disamarkan untuk etika penelitian; peran disesuaikan operasional toko (hanya **admin** dan **kasir** di sistem).*

---

## 3. Pedoman Pertanyaan Wawancara

### A. Kebiasaan kerja harian
1. Bagaimana cara Anda mencatat penjualan dan stok pupuk hari ini?  
2. Apa yang paling sering memakan waktu atau sering salah?  
3. Fitur apa yang paling penting jika ada sistem di komputer?

### B. Pengalaman membaca layar
4. Apakah pernah kesulitan membaca huruf kecil atau warna pudar di layar?  
5. Lebih nyaman layar terang atau gelap di ruang toko (siang hari)?  
6. Istilah apa yang mudah Anda pahami (misalnya “piutang”, “uang masuk”)?

### C. Uji tampilan tiga versi (ditunjukkan berurutan)
7. Versi **putih/polos** — apa yang dirasa kurang?  
8. Versi **gelap** — apa yang dirasa sulit?  
9. Versi **sekarang (krem/hijau)** — apa yang sudah enak dan apa yang masih perlu diperbaiki?

---

## 4. Ringkasan Hasil per Informan

### 4.1 Informan P1 — Pemilik toko (Bapak Harman, 52 tahun)

**Kebiasaan kerja**  
> “Catatan masih di buku besar sama Excel kadang-kadang. Kalau cari omzet bulan lalu, bolak-balik halaman. Piutang pelanggan kadang lupa kalau tidak ditulis jelas.”

**Masalah utama**  
- Sulit melihat total penjualan cepat.  
- Takut data hilang kalau buku rusak.  
- Mata cepat lelah jika huruf kecil.

**Umpan balik desain**

| Versi | Kutipan representatif | Penilaian singkat |
|-------|----------------------|------------------|
| A — Putih polos | “Putihnya silau, kayak kertas kosong. Tombolnya kurang kelihatan, saya bingung mau klik mana dulu.” | Kurang nyaman |
| B — Gelap | “Gelapnya bagus di malam, tapi siang di toko terang jadi susah baca. Huruf putihnya kadang pudar, saya harus dekat-dekat ke monitor.” | Kurang nyaman (siang) |
| C — Warm Neutral (sekarang) | “Warna krem ini enak di mata. Tulisannya hitam jelas. Tombol hijau untuk simpan saya langsung paham. Grafik laporan sudah kebaca, asal angkanya besar.” | Nyaman |

**Kebutuhan yang disepakati:** laporan penjualan jelas, piutang terpisah per pelanggan, angka rupiah pakai titik (215.000).

---

### 4.2 Informan P2 — Kasir (Ibu Siti, 48 tahun)

**Kebiasaan kerja**  
> “Yang tiap hari saya kerjakan: jual pupuk, hitung total, kadang pelanggan bayar sebagian. Sering antrean, jadi layar harus cepat dibaca.”

**Masalah utama**  
- Salah input qty karena huruf kecil di daftar barang.  
- Bingung jika banyak menu sekaligus.  
- Khawatir salah tekan tombol hapus.

**Umpan balik desain**

| Versi | Kutipan representatif | Penilaian singkat |
|-------|----------------------|------------------|
| A — Putih polos | “Tampak polos, tidak ada bedanya mana yang aktif. Kategori pupuk susah dibedakan.” | Kurang nyaman |
| B — Gelap | “Di kasir kami terang, layar gelap bikin mata capek. Tombol kategori hijau pudar, saya tidak yakin sudah diklik atau belum.” | Kurang nyaman |
| C — Warm Neutral (sekarang) | “Halaman jual pupuk (POS) sudah jelas. Tombol kategori kalau aktif hijau pekat, saya langsung tahu. Angka bayar pakai titik, saya tidak salah baca nol.” | Nyaman |

**Kebutuhan yang disepakati:** alur kasir singkat (cari barang → keranjang → bayar), tombol besar, konfirmasi sebelum simpan piutang.

---

### 4.3 Informan P3 — Admin / pengelola stok (Bapak Joko, 55 tahun)

**Kebiasaan kerja**  
> “Saya yang input barang masuk, cek stok habis, kadang ubah harga. Kalau sistem ribet, saya lebih nyambung ke buku dulu.”

**Masalah utama**  
- Sulit membedakan stok rendah di tabel panjang.  
- Istilah asing (misalnya “inventory”) tidak dipahami.  
- Form input harga kadang tidak jelas formatnya.

**Umpan balik desain**

| Versi | Kutipan representatif | Penilaian singkat |
|-------|----------------------|------------------|
| A — Putih polos | “Tabel barang terlalu rata, mata capek scroll. Tidak kelihatan mana stok menipis.” | Kurang nyaman |
| B — Gelap | “Kontras teks abu-abu dengan latar gelap kurang. Saya pakai kacamata, jadi tambah susah.” | Kurang nyaman |
| C — Warm Neutral (sekarang) | “Daftar barang lebih terbaca. Peringatan stok rendah warna oranye, saya paham. Label pakai bahasa Indonesia, tidak bingung.” | Nyaman |

**Kebutuhan yang disepakati:** filter stok rendah, nama barang jelas, harga format ribuan, istilah sederhana.

---

## 5. Tabel Sintesis Temuan Wawancara

| No | Tema | Temuan dari wawancara | Dampak ke desain |
|----|------|---------------------|------------------|
| T1 | Keterbacaan | Huruf kecil & kontras rendah menyebabkan salah baca angka | Ukuran font dinaikkan; kontras teks–latar diperkuat |
| T2 | Versi putih polos | Terasa “kosong”, hierarki lemah, silau | Ditingkatkan border, kartu, dan pemisah section |
| T3 | Versi gelap | Mata lelah di ruang terang; teks putih pudar | Dihindari sebagai tema utama; diganti Warm Neutral |
| T4 | Navigasi | Terlalu banyak menu membingungkan kasir | Menu disederhanakan per peran (admin/kasir) |
| T5 | Kasir / POS | Butuh tombol kategori & CTA yang jelas | Tab aktif solid hijau; tombol bayar menonjol |
| T6 | Laporan | Grafik dan tabel sulit dibaca pada versi gelap | Sumbu grafik & label disesuaikan tema terang |
| T7 | Kepercayaan | Takut salah hapus/ubah data | Konfirmasi piutang; tombol hapus warna merah jelas |
| T8 | Bahasa | Istilah teknis tidak dipahami | Label Indonesia: piutang, uang masuk, sisa hutang |
| T9 | Angka | Format ribuan tanpa titik membingungkan | Tampilan 215.000; angka 0 polos (tanpa garis diagonal) |

---

## 6. Perjalanan Desain Berdasarkan Masukan Wawancara

### Tahap 1 — Versi awal (putih polos / kurang elegan)

**Karakteristik:** latar putih dominan, sedikit variasi warna, tipografi standar kecil, hierarki visual lemah.

**Reaksi pengguna (gabungan P1–P3):**
- “Terlihat polos dan tidak menuntun mata.”
- “Sulit membedakan tombol utama dan sekunder.”
- “Cepat silau jika monitor terang.”

**Keputusan tim:** perlu identitas visual dan hierarki yang lebih jelas → eksperimen tema bertema aplikasi modern.

---

### Tahap 2 — Versi gelap

**Karakteristik:** latar hijau/tua gelap, teks terang, gaya “dashboard modern”.

**Reaksi pengguna (gabungan P1–P3):**
- “Di toko siang hari kontrasnya kurang nyaman.”
- “Teks putih/abu pada grafik laporan kurang jelas.”
- “Tombol dan tab kategori di kasir kurang menonjol (hijau pudar).”

**Keputusan tim:** tema gelap **tidak sesuai** konteks pengguna 50+ di ruang terang → direvisi total.

---

### Tahap 3 — Versi sekarang (Warm Neutral)

**Karakteristik:** latar krem `#F4F1EC`, teks coklat gelap, aksen hijau pertanian `#1A5C42`, peringatan oranye/merah fungsional.

**Reaksi pengguna (gabungan P1–P3):**
- “Mata lebih nyaman dipakai lama.”
- “Angka dan harga lebih mudah dibaca.”
- “Alur kasir dan laporan bisa dikerjakan tanpa tanya berulang ke anak karyawan.”

**Keputusan tim:** tema Warm Neutral dipilih sebagai **desain final** untuk skripsi dan UAT.

---

## 7. Transkrip Ringkas (cuplikan dialog)

**Pewawancara (P):** Pak Harman, kalau Bapak lihat tampilan yang putih polos tadi, bagian mana yang paling mengganggu?  
**Informan P1 (I):** Yang mengganggu itu saya tidak tahu tombol mana yang penting. Semuanya kayak sama. Kalau buru-buru mau lihat total penjualan, saya scroll-scroll bingung.

**P:** Lalu versi gelap tadi?  
**I:** Gelap itu kalau malam mungkin oke. Tapi kami jualan siang terang. Saya harus menutup setengah jendela supaya monitor kelihatan. Capek.

**P:** Ini versi terakhir, warna krem, tulisan gelap.  
**I:** Ini baru enak. Saya bisa baca “sisa hutang” tanpa salah. Tombol hijau untuk simpan saya berani klik.

---

**P:** Bu Siti, di halaman jual pupuk, versi gelap tadi bagaimana?  
**Informan P2 (I):** Tab pupuk kimia, pupuk organik, saya tidak yakin sudah aktif atau belum. Warna hijau muda di atas hijau tua. Sekarang kalau aktif hijau pekat, putih tulisannya, saya langsung tahu.

**P:** Nominal bayar pakai titik, membantu tidak?  
**I:** Membantu. Dulu 65000 saya baca bisa salah jadi 6500. Sekarang 65.000 jelas.

---

**P:** Pak Joko, untuk daftar barang, apa yang Bapak butuh?  
**Informan P3 (I):** Yang penting stok habis kelihatan. Versi putih dulu saya lewatkan baris habis. Versi gelap label abu-abu. Yang sekarang ada warna peringatan, saya senang.

---

## 8. Rekapitulasi Preferensi Desain (3 informan)

| Aspek | Versi A (putih) | Versi B (gelap) | Versi C (Warm Neutral) |
|-------|-----------------|-----------------|------------------------|
| Keterbacaan teks | 1 suka, 2 kurang | 0 suka, 3 kurang | 3 suka |
| Kenyamanan mata (siang) | 2 kurang | 3 kurang | 3 nyaman |
| Kejelasan tombol/aksi | 3 kurang | 2 kurang | 3 jelas |
| Kemudahan kasir (POS) | 2 kurang | 3 kurang | 3 mudah |
| Kemudahan laporan | 2 kurang | 3 kurang | 3 cukup mudah |
| **Rekomendasi final** | Tidak dipilih | Tidak dipilih | **Dipilih (100%)** |

*Skala: 3 informan; “suka/nyaman/jelas/mudah” = layak tanpa bantuan berulang.*

---

## 9. Kesimpulan Hasil Wawancara

Wawancara dengan pemilik toko, kasir, dan pengelola stok menunjukkan bahwa kebutuhan utama pengguna bukan sekadar fitur lengkap, melainkan **keterbacaan**, **kejelasan alur**, dan **kepercayaan diri** saat beroperasi di layar. Versi awal yang putih polos dinilai kurang menuntun pengguna; versi gelap dinilai melelahkan mata pada kondisi pencahayaan toko siang hari. Versi **Warm Neutral** mendapat respons positif karena kontras teks–latar seimbang, tombol aksi menonjol, istilah berbahasa Indonesia, dan format angka yang sesuai kebiasaan (titik ribuan).

Temuan wawancara ini menjadi dasar iterasi antarmuka dan selaras dengan pengujian HCD serta pengembangan modul kasir, hutang, dan laporan pada sistem inventaris pupuk.

---

## 10. Lampiran untuk Skripsi

- [ ] Form persetujuan informan (lembar terpisah)  
- [ ] Foto/tangkapan layar Versi A, B, C (3–5 gambar)  
- [ ] Tabel rekapitulasi di atas → bisa jadikan **Tabel 4.x Hasil Wawancara**  
- [ ] Cuplikan transkrip → **Lampiran** (2–3 halaman)

---

*Dokumen ini melengkapi `docs/LAPORAN_PENGUJIAN_HCD_DAN_BLACKBOX.md` (Bab 4.5.1). Sesuaikan nomor tabel dengan daftar isi skripsi Anda.*
