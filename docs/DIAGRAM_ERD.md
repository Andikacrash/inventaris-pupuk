# Diagram Entity Relationship (ERD)
## Sistem Inventaris dan POS Pupuk

Diagram ini memodelkan entitas, atribut utama, dan relasi pada level **konseptual/logis** sesuai basis data aplikasi Laravel (`inventaris-pupuk`).

---

## Diagram ERD — dbdiagram.io (tempel langsung)

1. Buka [https://dbdiagram.io](https://dbdiagram.io)
2. Hapus contoh kode di panel kiri
3. Buka file **`docs/DIAGRAM_ERD.dbml`** → salin semua isinya → tempel di dbdiagram.io
4. Diagram muncul otomatis di kanan → **Export** → PNG / PDF / SQL

Atau: menu **Import** → paste DBML.

---

## Diagram ERD — Notasi Chen (seperti contoh skripsi)

Buka file interaktif di browser (persegi hijau = entitas, belah ketupat biru = relasi, oval merah muda = atribut):

**[`docs/DIAGRAM_ERD_CHEN.html`](DIAGRAM_ERD_CHEN.html)**

Cara ekspor: buka file → `Ctrl+P` → Simpan sebagai PDF/PNG.

---

## Diagram ERD (Mermaid)

Salin kode di bawah ke [Mermaid Live Editor](https://mermaid.live) atau plugin Mermaid di Word/Markdown untuk menghasilkan gambar.

```mermaid
erDiagram
    USERS ||--o{ SALES : "melakukan"
    USERS ||--o{ STOCK_MOVEMENTS : "mencatat"
    USERS ||--o{ DEBTS : "mengelola"
    USERS ||--o{ DEBT_PAYMENTS : "mencatat"
    USERS ||--o{ INSTALLMENT_PLANS : "membuat"
    USERS ||--o{ INSTALLMENT_PAYMENTS : "mencatat"

    CATEGORIES ||--o{ PRODUCTS : "memiliki"
    SUPPLIERS ||--o{ PRODUCTS : "menyediakan"

    PRODUCTS ||--o{ STOCK_MOVEMENTS : "memiliki"
    PRODUCTS ||--o{ SALE_ITEMS : "terjual melalui"

    SALES ||--|{ SALE_ITEMS : "memiliki"
    SALES ||--o| DEBTS : "menghasilkan"

    DEBTS ||--o{ DEBT_PAYMENTS : "menerima"
    DEBTS ||--o{ INSTALLMENT_PLANS : "memiliki"
    DEBTS ||--o{ INSTALLMENT_PAYMENTS : "memiliki"

    INSTALLMENT_PLANS ||--o{ INSTALLMENT_PAYMENTS : "memiliki"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "admin, kasir, manager"
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        bigint id PK
        string name
        text description
        smallint sort_order
        timestamp created_at
        timestamp updated_at
    }

    SUPPLIERS {
        bigint id PK
        string name
        string contact_person
        string phone
        string email
        text address
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        bigint id PK
        string name
        string brand
        enum type "organik, kimia"
        enum unit "kg, liter, karung"
        decimal price
        int stock_quantity
        int minimum_stock
        text description
        string image
        string barcode UK
        bigint category_id FK
        bigint supplier_id FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    SALES {
        bigint id PK
        string invoice_number UK
        string customer_name
        string customer_phone
        date sale_date
        decimal discount
        decimal total_amount
        enum payment_method "cash, transfer, card, credit"
        decimal payment_amount
        decimal change_amount
        decimal debt_amount
        enum debt_status
        enum status "pending, completed, cancelled"
        string delivery_method
        text delivery_address
        string delivery_phone
        bigint user_id FK
        timestamp deleted_at "soft delete"
        timestamp created_at
        timestamp updated_at
    }

    SALE_ITEMS {
        bigint id PK
        bigint sale_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
        decimal subtotal
        timestamp created_at
        timestamp updated_at
    }

    STOCK_MOVEMENTS {
        bigint id PK
        bigint product_id FK
        enum type "in, out"
        int quantity
        string reference_type "sale, purchase, adjustment"
        bigint reference_id
        text notes
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }

    DEBTS {
        bigint id PK
        bigint sale_id FK UK
        string customer_name
        string customer_phone
        decimal total_amount
        decimal paid_amount
        decimal remaining_amount
        date due_date
        enum status "unpaid, partial, paid"
        text notes
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }

    DEBT_PAYMENTS {
        bigint id PK
        bigint debt_id FK
        decimal amount
        date payment_date
        enum payment_method "cash, transfer, card"
        text notes
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }

    INSTALLMENT_PLANS {
        bigint id PK
        bigint debt_id FK
        decimal total_amount
        int installment_count
        decimal installment_amount
        enum frequency "daily, weekly, monthly"
        date start_date
        date end_date
        decimal paid_amount
        int paid_count
        enum status "active, completed, cancelled"
        text notes
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }

    INSTALLMENT_PAYMENTS {
        bigint id PK
        bigint installment_plan_id FK
        bigint debt_id FK
        int installment_number
        decimal amount
        date due_date
        date payment_date
        enum status "pending, paid, overdue, skipped"
        enum payment_method
        text notes
        bigint user_id FK "nullable"
        timestamp created_at
        timestamp updated_at
    }
```

---

## Keterangan Simbol

| Simbol Mermaid | Makna |
|----------------|--------|
| `\|\|--o{` | One to Many (1:N) |
| `\|\|--o\|` | One to One (1:1) |
| PK | Primary Key |
| FK | Foreign Key |
| UK | Unique Key |

---

## Tabel 4.2 Tipe Relasi (ringkasan)

| No | Entitas 1 | Relasi | Entitas 2 | Kardinalitas |
|:---:|-----------|--------|-----------|:------------:|
| 1 | Users | Melakukan | Sales | 1 : N |
| 2 | Users | Mencatat | Stock_Movements | 1 : N |
| 3 | Users | Mengelola | Debts | 1 : N |
| 4 | Users | Mencatat | Debt_Payments | 1 : N |
| 5 | Users | Membuat | Installment_Plans | 1 : N |
| 6 | Users | Mencatat | Installment_Payments | 1 : N |
| 7 | Categories | Memiliki | Products | 1 : N |
| 8 | Suppliers | Menyediakan | Products | 1 : N |
| 9 | Products | Memiliki | Stock_Movements | 1 : N |
| 10 | Sales | Memiliki | Sale_Items | 1 : N |
| 11 | Sale_Items | Merujuk pada | Sales | N : 1 |
| 12 | Sale_Items | Merujuk pada | Products | N : 1 |
| 13 | Sales | Menghasilkan | Debts | 1 : 1 |
| 14 | Debts | Menerima | Debt_Payments | 1 : N |
| 15 | Debts | Memiliki | Installment_Plans | 1 : N |
| 16 | Installment_Plans | Memiliki | Installment_Payments | 1 : N |
| 17 | Installment_Payments | Merujuk pada | Debts | N : 1 |

---

## Narasi ERD

Entity Relationship Diagram (ERD) memodelkan konsep basis data sistem inventaris dan penjualan pupuk. Entitas inti meliputi **users** (pengguna sistem), **categories** dan **products** (master barang), **suppliers** (pemasok), **sales** dan **sale_items** (transaksi penjualan), **stock_movements** (audit stok), serta **debts**, **debt_payments**, **installment_plans**, dan **installment_payments** (modul piutang/hutang pelanggan).

Relasi **Sales–Sale_Items–Products** membentuk pola many-to-many yang diimplementasikan melalui tabel penghubung **sale_items**. Relasi **Sales–Debts** bersifat one-to-one karena satu transaksi kredit menghasilkan satu record hutang. **Stock_movements** merekam setiap masuk/keluar stok dan dapat merujuk transaksi lain melalui atribut `reference_type` dan `reference_id` (misalnya penjualan).

Diagram ini bersifat level konseptual/logis; detail tipe data fisik, indeks, dan constraint dapat berbeda sedikit pada implementasi migrasi Laravel.

---

## Cara mengekspor ke gambar (untuk skripsi)

1. Buka https://mermaid.live  
2. Tempel blok kode `mermaid` di atas  
3. Klik **Export** → PNG/SVG  
4. Sisipkan ke dokumen Word sebagai **Gambar 4.x Entity Relationship Diagram**

Alternatif: gunakan draw.io / dbdiagram.io dengan mengacu tabel relasi di atas.
