#import "@preview/codly:1.3.0": *
#import "@preview/codly-languages:0.1.1": *

#set page(
  paper: "a4",
  margin: (left: 2cm, right: 2cm, top: 2cm, bottom: 2cm),
  footer: context [
    #h(1fr)
    #counter(page).display(
      "1/1",
      both: true,
    )
  ],
)

#set text(
  font: "New Computer Modern",
  size: 12pt,
)

#set heading(numbering: "1.1")
#show heading: set block(below: 1em)

#show: codly-init.with()
#codly(languages: codly-languages)

// ------------------------------------------------------------------ title

#let paper_title = "Rencana Mockup dan Table SQL UAS"
#let paper_subtitle = "Pemrograman Mobile Lanjut — Week 12"
#let paper_authors = "24410100024 Muhammad Ghifari Lazawardi"

#text(size: 28pt, weight: 900)[#paper_title] \
#text(size: 18pt, weight: 600)[#paper_subtitle]

#paper_authors

#line(length: 100%)

// ------------------------------------------------------------------ functions

#let task-counter = counter("task")

#let task-box(body) = {
  task-counter.step()
  rect(
    fill: rgb("#e6f3ff"),
    stroke: (
      bottom: (paint: black, thickness: 1pt, dash: "dashed"),
      rest: none,
    ),
    radius: 3pt,
    inset: 8pt,
    width: 100%,
  )[
    *Soal #context task-counter.display():* #body
  ]
}

#let csv_table(csv_path, delimiter: ";", columns: auto, align: auto, ..table_args) = {
  let csv_data = csv(csv_path, delimiter: delimiter)

  // Auto-detect columns from first row
  let col_count = if columns == auto {
    csv_data.first().len()
  } else {
    columns
  }

  // Create default alignment (left for all columns)
  let col_align = if align == auto {
    (left,) * col_count
  } else {
    align
  }

  table(
    columns: col_count,
    align: col_align,
    stroke: 0.25pt,
    ..table_args.named(),
    table.header(..csv_data.first().map(x => [*#x*])),
    ..csv_data.slice(1).flatten(),
  )
}

#set par(
  // first-line-indent: (
  //   amount: 1.1em,
  //   all: true,
  // ),
  spacing: 1.5em,
  leading: 1em,
  justify: true,
  linebreaks: "optimized",
)

// ------------------------------------------------------------------ contents
// content starts

= Mockup Aplikasi

== Nama Proyek

Aplikasi Point of Sales (POS) Restoran Sederhana

== Deskripsi

Mockup ini merancang antarmuka pengguna (UI) aplikasi mobile untuk kasir atau resepsionis restoran. Desain difokuskan pada kesederhanaan dan kecepatan transaksi. Alur pengguna dimulai dari Input Menu (memilih item dan jumlah), berlanjut ke Order Summary (konfirmasi pesanan dan perhitungan pajak), masuk ke Metode Pembayaran (simulasi scan QRIS), dan diakhiri dengan pencatatan di Order History (riwayat transaksi). Desain menggunakan gaya minimalis dengan tombol aksi (Call to Action) yang jelas untuk meminimalkan kesalahan pengguna.

== Desain Mockup

#figure(
  image("./cropped_screenshots/Screenshot 2025-12-08 152628.png", height: 35%),
  caption: [
    *Tampilan Input Menu*. Antarmuka pemilihan menu yang menampilkan daftar makanan beserta harga dan gambar thumbnail. Dilengkapi dengan kontrol stepper (+/-) untuk mengatur kuantitas pesanan dengan cepat dan tombol "Add" yang responsif. Dibawah, ada tombol "View Order" untuk melihat ringkasan pemesanan sebelum transaksi.
  ],
)

#figure(
  image("./cropped_screenshots/Screenshot 2025-12-08 152632.png", height: 35%),
  caption: [
    *Ringkasan Pesanan (Order Summary)*. Halaman konfirmasi yang menampilkan daftar item dalam keranjang sebelum checkout. Halaman ini secara otomatis menghitung Subtotal, Pajak (10%), dan Total Akhir untuk transparansi biaya.
  ],
)

#figure(
  image("./cropped_screenshots/Screenshot 2025-12-08 152641.png", height: 35%),
  caption: [
    *Halaman Pembayaran*. Simulasi metode pembayaran non-tunai menggunakan QRIS. QRIS untuk project ini hanya sekedar random text. Tampilan dibuat fokus pada kode QR dengan status pembayaran yang jelas ("Waiting" vs "Success") untuk memudahkan verifikasi kasir.
  ],
)

#figure(
  image("./cropped_screenshots/Screenshot 2025-12-08 152646.png", height: 35%),
  caption: [
    *Konfimasi Pembayaran Berhasil dan Opsi Cetak Nota*.
  ],
)

#figure(
  image("./cropped_screenshots/Screenshot 2025-12-08 152651.png", height: 35%),
  caption: [
    *Riwayat Pesanan (Order History)*. Fitur pencatatan transaksi yang menampilkan daftar pesanan yang telah selesai (Paid). Setiap kartu riwayat memuat detail item, total biaya, waktu transaksi, serta opsi untuk mencetak ulang struk (Reprint Bill).
  ],
)

= Database Schema

#set table(
  fill: (x, y) => if y == 0 {
    gray.lighten(50%)
  },
  align: left,
)

Berikut adalah rancangan tabel yang dibutuhkan untuk mendukung fitur Menu, Cart, Pembayaran, dan Riwayat Transaksi.

== Tabel

=== Tabel `menu_items`

Tabel ini menyimpan data semua makanan dan minuman yang tersedia.

#align(center)[
  #table(
    columns: 3,
    stroke: 0.25pt,
    table.header([*Kolom*], [*Tipe Data*], [*Keterangan*]),
    [`id`], [`INTEGER` (PK)], [ID unik untuk setiap menu (Auto Increment).],
    [`name`], [`TEXT`], [Nama menu (contoh: "Nasi Goreng Special").],
    [`price`], [`DECIMAL`], [Harga satuan menu.],
    [`image_asset`], [`TEXT`], [Path atau nama file gambar icon (contoh: "rice").],
  )
]

=== Tabel `orders`

Tabel ini menyimpan data transaksi. Satu baris di sini mewakili satu kali pembayaran/struk.

#align(center)[
  #table(
    columns: 3,
    stroke: 0.25pt,
    table.header([*Kolom*], [*Tipe Data*], [*Keterangan*]),
    [`id`], [`INTEGER` (PK)], [ID unik order (bisa digunakan sebagai Nomor Struk).],
    [`order_date`], [`DATETIME`], [Tanggal dan waktu transaksi dibuat.],
    [`subtotal`], [`INTEGER`], [Total harga sebelum pajak.],
    [`tax_amount`], [`INTEGER`], [Nilai pajak (10\%).],
    [`total_amount`], [`INTEGER`], [Total akhir yang harus dibayar.],
    [`payment_method`], [`TEXT`], [Metode pembayaran (contoh: "QRIS", "CASH").],
    [`payment_status`], [`TEXT`], [Status pembayaran ("PENDING", "PAID").],
  )
]

=== Tabel `order_items`

Tabel ini menyimpan rincian barang apa saja yang dibeli dalam satu Order (`Detail`).

#align(center)[
  #table(
    columns: 3,
    stroke: 0.25pt,
    table.header([*Kolom*], [*Tipe Data*], [*Keterangan*]),
    [`id`], [`INTEGER` (PK)], [ID unik untuk baris item ini.],
    [`order_id`], [`INTEGER` (FK)], [*Foreign Key* yang merujuk ke tabel `orders`.],
    [`menu_item_id`], [`INTEGER` (FK)], [*Foreign Key* yang merujuk ke tabel `menu_items`.],
    [`menu_name`], [`TEXT`], [Menyimpan nama menu saat transaksi (snapshot).\*],
    [`quantity`], [`INTEGER`], [Jumlah item yang dibeli.],
    [`price_at_time`], [`INTEGER`], [Harga satuan saat transaksi terjadi (snapshot).\*],
    [`subtotal`], [`INTEGER`], [`quantity` \* `price_at_time`.],
  )
]

\* Catatan Penting (Snapshot): Kolom `menu_name` dan `price_at_time` di tabel `order_items` sangat penting. Jika di masa depan harga item (contoh: Nasi Goreng) naik di tabel `menu_items`, riwayat transaksi lama tidak boleh berubah. Oleh karena itu, disini saya menyalin (snapshot) harga dan nama ke dalam tabel `order` saat transaksi terjadi.

== Entity Relationship Diagram (ERD)

#figure(
  image("./erd.svg", width: 80%),
  caption: [
    Diagram ERD untuk Proyek POS
  ],
)

== Contoh Data

=== `menu_items`

```sql
INSERT INTO menu_items (id, name, price, image_asset) VALUES
(1, 'Nasi Goreng Special', 25000, 'rice'),
(2, 'Iced Tea Jumbo', 8000, 'glass-water');
```

=== `orders` (Satu Transaksi)

```sql
INSERT INTO orders (id, order_date, total_amount, payment_status) VALUES 
(1001, '2024-12-08 14:30:00', 36300, 'PAID');
```

=== order_items (Isi Transaksi `orders.id = 1001`)

```sql
-- Membeli 1 Nasi Goreng
INSERT INTO order_items (order_id, menu_item_id, menu_name, quantity, price_at_time) VALUES 
(1001, 1, 'Nasi Goreng Special', 1, 25000);

-- Membeli 1 Es Teh
INSERT INTO order_items (order_id, menu_item_id, menu_name, quantity, price_at_time) VALUES 
(1001, 2, 'Iced Tea Jumbo', 1, 8000);
```

// content ends
// ------------------------------------------------------------------ contents

// #pagebreak()
// #bibliography("references.bib", style: "apa", title: "Daftar Referensi")
