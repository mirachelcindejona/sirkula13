<p align="center">
  <img src="public/images/logo.png" width="88" alt="Logo SIRKULA RW 13">
</p>

<h1 align="center">SIRKULA RW 13</h1>

<p align="center">
  Sistem informasi bank sampah dan partisipasi warga<br>
  RW 13, Kelurahan Babakan Sari, Kecamatan Kiaracondong, Kota Bandung
</p>

SIRKULA mencatat setoran sampah, poin warga, stok material, kas RW, kegiatan, dan donasi dalam satu aplikasi. Warga melihat riwayatnya sendiri. Petugas mengerjakan bagian yang menjadi tugasnya. Halaman publik tetap bisa dibuka tanpa masuk.

## Yang dikerjakan aplikasi

**Bank sampah.** Operator mencatat setoran warga RW 13 atau penyetor dari luar. Jumlah mengikuti satuan jenis material: kg, g, liter, pcs, lembar, atau ikat. Setoran yang diterima langsung masuk catatan, beserta EcoPoints. Material yang ditolak tidak menghasilkan poin dan tidak dibayar. Setoran jual menunggu pencairan. Setoran donasi material memberi bonus poin dan tidak dibayar uang.

**Stok.** Stok dihitung dari setoran yang diterima dikurangi material yang sudah keluar ke pengepul, produk, atau tujuan lain.

**Keuangan.** Bendahara mencairkan tabungan setoran, termasuk pencairan sebagian. Pencairan terakhir dapat dikoreksi dalam 24 jam. Buku kas memisahkan pemasukan dan pengeluaran. Donasi uang baru masuk kas setelah petugas memastikan uangnya diterima. SupportPoints dari donasi uang terpisah dari EcoPoints.

**Kegiatan dan produk.** Pengelola membuat kegiatan, mengesahkan kehadiran, menayangkan katalog, dan menindaklanjuti pesanan. Poin kehadiran masuk setelah kehadiran disahkan.

**Admin.** Superadmin menyetujui pendaftaran warga, mengatur hak akses petugas, tarif, jenis material, katalog reward, serta melihat pesanan yang ditahan dan riwayat aktivitas.

## Peran

| Peran | Tugas di aplikasi |
| --- | --- |
| Warga | Melihat profil, riwayat setoran, EcoPoints, reward, dan pesanan sendiri |
| Operator bank sampah | Mencatat setoran, memantau stok, menyerahkan reward, dan mencairkan tabungan |
| Pengelola kegiatan | Mengelola kegiatan, kehadiran, katalog, dan pesanan |
| Bendahara | Mengurus pencairan, buku kas, donasi, rekening, QRIS, dan laporan |
| Superadmin | Mengawasi seluruh modul, akun, tarif, dan audit |

Setelah masuk, tiap peran diarahkan ke beranda sesuai tugasnya.

## Halaman yang terbuka untuk umum

| Halaman | Alamat |
| --- | --- |
| Beranda | `/` |
| Profil RW | `/profil-rw` |
| Panduan pemilahan | `/panduan` |
| Kegiatan warga | `/kegiatan` |
| Katalog produk | `/katalog` |
| Peringkat | `/peringkat` |
| Donasi dan dukungan | `/donasi` |
| Masuk | `/masuk` |
| Daftar warga | `/daftar` |

Pendaftaran warga memakai email. Akun baru dapat dipakai setelah diperiksa pengurus. Pengunjung yang sudah masuk dan membuka `/` diarahkan ke beranda perannya.

## Menjalankan di komputer

Yang diperlukan:

- PHP 8.3
- Composer
- MySQL
- Node.js, untuk membangun aset bawaan Laravel

Dari folder aplikasi:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Di `.env`, arahkan database ke MySQL lokal. Untuk Laragon, nilai yang biasa dipakai:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sirkula13
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `sirkula13`, lalu siapkan tabel, data awal, dan tautan berkas:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000. `APP_URL` sebaiknya sama dengan alamat yang dibuka di peramban, supaya foto yang disimpan lewat storage tampil.

`php artisan db:seed` mengisi peran, jenis material, dan data contoh. Seeder contoh menolak berjalan saat `APP_ENV=production`.

## Akun contoh

Akun ini hanya untuk pengembangan. Kata sandinya sama untuk semua akun dan tertulis di `database/seeders/SirkulaDemoSeeder.php`.

| Email | Peran | Keadaan |
| --- | --- | --- |
| `admin@sirkula.test` | Superadmin | Aktif |
| `rudi@sirkula.test` | Operator bank sampah | Aktif |
| `yanti@sirkula.test` | Operator bank sampah | Aktif |
| `maya@sirkula.test` | Pengelola kegiatan | Aktif |
| `nisa@sirkula.test` | Pengelola kegiatan | Aktif |
| `lina@sirkula.test` | Bendahara | Aktif |
| `sari@sirkula.test` | Warga | Aktif |
| `budi@sirkula.test` | Warga | Aktif |
| `diah@sirkula.test` | Warga | Aktif |
| `rina@sirkula.test` | Warga | Menunggu persetujuan |
| `deni@sirkula.test` | Warga | Perlu informasi tambahan |

Rekening, kontak donasi, dan gambar QRIS pada data contoh juga ikut terisi, supaya halaman donasi langsung terlihat siap dipakai. Ganti isinya lewat menu **Rekening & QRIS** sebelum aplikasi dipakai sungguhan.

## Foto

Foto setoran, produk, kegiatan, reward, bukti donasi, dan QRIS disimpan di disk `public` (`storage/app/public`). Jenis yang diterima JPG, PNG, dan WEBP, paling besar 2 MB. Setiap unggahan menampilkan pratinjau sebelum disimpan.

## Peta singkat

```text
app/Http/Controllers     Alur setoran, kas, kegiatan, admin, dan halaman publik
app/Support              Label, unggahan, navigasi, dan aturan tampilan
routes/modules           Rute per bagian: publik, akun, bank sampah, kegiatan, keuangan, admin
resources/views/pages    Halaman Blade
public/css/sirkula.css   Tampilan aplikasi
public/js/sirkula-ui.js  Pencarian pada dropdown, pratinjau foto, dan tampilan kata sandi
database/seeders         Peran, material, dan data contoh
```

## Uji

```bash
php artisan test
```
