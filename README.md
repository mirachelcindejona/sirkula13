<p align="center">
  <img src="public/images/logo.png" width="88" alt="Logo SIRKULA RW 13">
</p>

<h1 align="center">SIRKULA RW 13</h1>

<p align="center">
  Catatan bank sampah dan kegiatan warga<br>
  RW 13 Babakan Sari, Kiaracondong, Bandung
</p>

SIRKULA dipakai pengurus dan warga untuk mencatat setoran, poin, stok, kas, kegiatan, dan donasi. Warga melihat riwayatnya sendiri. Petugas hanya mengerjakan bagian tugasnya. Beranda, panduan, kegiatan, katalog, peringkat, dan donasi bisa dibuka tanpa masuk.

## Yang tercatat di sini

Setoran langsung masuk catatan begitu petugas menyimpannya. Jumlah mengikuti satuan material: kg, g, liter, pcs, lembar, atau ikat. Setoran yang diterima dapat poin. Yang ditolak tidak dapat poin dan tidak dibayar. Setoran jual ditabung dulu, lalu dicairkan. Setoran donasi material dapat bonus poin, tanpa uang.

Stok dihitung dari setoran yang diterima dikurangi material yang sudah keluar.

Bendahara mencairkan tabungan, termasuk sebagian. Pencairan terakhir masih bisa dikoreksi dalam 24 jam. Buku kas membedakan pemasukan dan pengeluaran. Donasi uang baru masuk kas setelah bendahara memastikan uangnya sudah diterima. Poin dari donasi uang tidak digabung dengan EcoPoints.

Pengelola membuat kegiatan, mengesahkan kehadiran, menayangkan katalog, dan mengurus pesanan. Poin hadir masuk setelah kehadiran disahkan. Di akun warga, status kegiatan ikut berubah menjadi hadir.

Superadmin menyetujui atau menolak pendaftaran, mengatur petugas, tarif, jenis material, katalog reward, serta melihat pesanan yang ditahan dan riwayat aktivitas.

## Siapa memakai apa

| Peran | Di aplikasi |
| --- | --- |
| Warga | Profil, setoran, poin, reward, dan pesanan sendiri |
| Operator bank sampah | Setoran, stok, serah terima reward, pencairan |
| Pengelola kegiatan | Kegiatan, kehadiran, katalog, pesanan |
| Bendahara | Pencairan, buku kas, donasi, rekening, QRIS, laporan |
| Superadmin | Akun, tarif, dan seluruh bagian di atas |

Akun yang baru daftar bisa masuk dan melihat halaman warga, tetapi ID belum terbit. Menyetor, menukar poin, mendaftar kegiatan, dan memesan produk menunggu persetujuan pengurus. Setelah masuk, tiap peran dibawa ke beranda tugasnya.

## Halaman umum

| Halaman | Alamat |
| --- | --- |
| Beranda | `/` |
| Profil RW | `/profil-rw` |
| Panduan pemilahan | `/panduan` |
| Kegiatan | `/kegiatan` |
| Katalog produk | `/katalog` |
| Peringkat | `/peringkat` |
| Donasi | `/donasi` |
| Masuk | `/masuk` |
| Daftar | `/daftar` |

Yang sudah masuk lalu membuka `/` dibawa ke beranda perannya.

## Menjalankan di komputer

Perlu PHP 8.3, Composer, MySQL, dan Node.js.

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Di `.env`, arahkan ke database MySQL. Untuk Laragon biasanya seperti ini:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sirkula13
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `sirkula13`, lalu:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000. `APP_URL` sebaiknya sama dengan alamat di peramban, supaya foto tampil.

`php artisan db:seed` mengisi peran, jenis material, dan data contoh. Seeder contoh tidak jalan kalau `APP_ENV=production`.

## Akun contoh

Hanya untuk dicoba di komputer sendiri. Kata sandinya sama untuk semua akun, tertulis di `database/seeders/SirkulaDemoSeeder.php`.

| Email | Peran | Keadaan |
| --- | --- | --- |
| `admin@sirkula.test` | Superadmin | Aktif |
| `rudi@sirkula.test` | Operator | Aktif |
| `yanti@sirkula.test` | Operator | Aktif |
| `maya@sirkula.test` | Pengelola kegiatan | Aktif |
| `nisa@sirkula.test` | Pengelola kegiatan | Aktif |
| `lina@sirkula.test` | Bendahara | Aktif |
| `sari@sirkula.test` | Warga | Aktif |
| `budi@sirkula.test` | Warga | Aktif |
| `diah@sirkula.test` | Warga | Aktif |
| `rina@sirkula.test` | Warga | Belum disetujui |
| `deni@sirkula.test` | Warga | Belum disetujui |

Rekening, kontak, dan gambar QRIS contoh juga sudah terisi. Ganti lewat menu Rekening & QRIS sebelum dipakai sungguhan.

## Email lupa kata sandi

Surat dari aplikasi dikirim atas nama sirkulacerdas.aksara2026@gmail.com. Di `.env`, isi `MAIL_PASSWORD` dengan sandi aplikasi Gmail akun itu (16 karakter dari setelan keamanan Google, bukan kata sandi untuk masuk Gmail). Selama sandi itu kosong, petunjuk lupa kata sandi tidak dikirim.

Email yang tidak terdaftar ditolak. Email yang terdaftar mendapat tautan atur ulang, berlaku 60 menit.

## Foto

Foto disimpan di `storage/app/public`. Yang diterima JPG, PNG, dan WEBP, paling besar 2 MB. Sebelum disimpan, ada pratinjau.

## Folder yang sering dibuka

```text
app/Http/Controllers     Setoran, kas, kegiatan, admin, halaman publik
app/Support              Label, unggahan, dan menu
routes/modules           Rute per bagian
resources/views/pages    Halaman
public/css/sirkula.css   Tampilan
public/js/sirkula-ui.js  Dropdown, pratinjau foto, pesan isian
database/seeders         Peran, material, data contoh
```

## Uji

```bash
php artisan test
```
