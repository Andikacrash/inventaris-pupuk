# UI Elegance Checklist (Preview)

Dokumen ini dipakai untuk menilai kesan "elegan" secara objektif, bukan selera saja.

## Skor Cepat (Saat Ini)

- Dashboard: 8/10
- Kasir: 6.5/10
- Kelola Barang: 8.5/10
- Manajemen Hutang: 8/10
- Laporan Penjualan: 8/10
- Laporan Stok: 8.5/10

## Patokan Wajib Elegan

- Konsistensi spacing (jarak vertikal seragam 8/12/16/24)
- Typography jelas (judul, subjudul, label, body)
- Warna utama tidak lebih dari 1-2 aksen
- Status warna hanya untuk makna (success/warning/danger)
- Tabel dan filter punya style yang sama antar halaman
- Komponen tombol seragam (radius, hover, ketebalan font)
- Hindari campuran gaya ikon (emoji + icon set)

## Temuan Utama

- **Kasir masih campur gaya**: banyak emoji dan style khusus yang berbeda jauh dari halaman lain.
- **Laporan sudah membaik**, tapi masih ada beberapa action button yang sebelumnya terlalu berwarna.
- **Bahasa komponen belum 100% satu sistem**: beberapa card/filter menggunakan class berbeda dengan hasil visual mirip.

## Rencana Penyelesaian Bertahap

### Tahap 1 - Finalisasi Dasar (cepat)

- Samakan semua button action tabel jadi `outline` style.
- Samakan tabel detail modal ke `product-table`.
- Audit heading/subheading agar skala font konsisten.

### Tahap 2 - Unified Design System

- Buat token spacing/typography sederhana (size/weight/line-height).
- Buat utilitas UI internal: `premium-panel`, `premium-filter-card`, `premium-table-card`.
- Terapkan ke semua halaman laporan + dashboard + hutang.

### Tahap 3 - Kasir Premium Pass

- Ganti emoji yang tampil langsung di UI utama dengan Font Awesome.
- Samakan style input/search/filter kasir dengan komponen global.
- Rapikan hierarchy visual di ringkasan pembayaran.

## Definisi "Selesai"

- Tidak ada lagi halaman yang terasa "template berbeda"
- User bisa pindah halaman tanpa merasa style berubah drastis
- Warna terlihat tenang, informasi penting tetap menonjol
