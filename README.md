<p align="center">
  <img src="public/favicon.svg" alt="Velora" width="240">
</p>
<h1 align='center'>
  Velora - Vehicle Booking Web App
</h1>

<p align="center">
  <img alt="PHP 8.5.0" src="https://img.shields.io/badge/PHP_8.5.0-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="Laravel 13" src="https://img.shields.io/badge/Laravel_13.29-FF2D20?style=flat-square&logo=laravel&logoColor=white">
  <img alt="MySQL 8.4" src="https://img.shields.io/badge/MySQL_8.4-4479A1?style=flat-square&logo=mysql&logoColor=white">
  <img alt="Inertia v3" src="https://img.shields.io/badge/Inertia_v3-9553E9?style=flat-square&logo=inertia&logoColor=white">
  <img alt="React 19" src="https://img.shields.io/badge/React_19.2-20232A?style=flat-square&logo=react&logoColor=61DAFB">
  <img alt="TypeScript 5.7" src="https://img.shields.io/badge/TypeScript_5.7-3178C6?style=flat-square&logo=typescript&logoColor=white">
  <img alt="Tailwind CSS 4" src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white">
  <img alt="Vite 8" src="https://img.shields.io/badge/Vite_8-646CFF?style=flat-square&logo=vite&logoColor=white">
  <img alt="Node.js 24" src="https://img.shields.io/badge/Node.js_24-339933?style=flat-square&logo=nodedotjs&logoColor=white">
  <img alt="Docker" src="https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white">
</p>

<br />

Velora adalah aplikasi **manajemen pemesanan kendaraan** berbasis web yang menangani alur pemesanan kendaraan, rantai persetujuan berjenjang (penyetuju), ekspor laporan ke Excel, serta dashboard konsumsi BBM dan jadwal servis.

## Akun Default (Seeder)

Semua akun memiliki kata sandi **`password`**.

| Role | Nama | Email |
| --- | --- | --- |
| Admin (kelola pemesanan & laporan) | Admin Utama | `admin@example.com` |
| Penyetuju (persetujuan berjenjang) | Penyetuju Satu | `penyetuju@example.com` |

> Selain itu seeder membuat akun acak (email faker, kata sandi tetap `password`): 2 admin & 7 penyetuju
> tambahan, 9 driver, 23 kendaraan, serta data dummy widget dashboard (sebaran pemesanan, BBM, jadwal service).

## Cara Menjalankan (Docker)

Aplikasi dikemas sebagai tiga layanan: **db** (MySQL), **app** (Laravel + PHP-FPM), dan **server** (Nginx).

```bash
#1. Bangun image dan jalankan semua layanan
docker compose up -d --build

#2. Akses aplikasi
#   http://localhost:8080
```

## Cara Menjalankan (Tanpa Docker)

Butuh PHP 8.5, MySQL, Node.js ≥ 20, dan Composer terpasang di mesin.

```bash
#1. Instal dependensi
composer install
npm install

#2. Siapkan environment & kunci aplikasi
cp .env.example .env
php artisan key:generate

#3. Atur koneksi MySQL di file .env (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

#4. Buat database, lalu jalankan migrasi + seeder
php artisan migrate
php artisan db:seed

#5. Jalankan aplikasi (dua terminal, atau gunakan `composer run dev`)
npm run dev # mode pengembangan (HMR)
php artisan serve # akses http://localhost:8000
```

## Panduan Penggunaan

### 1. Masuk Aplikasi

- Buka alamat aplikasi, lalu klik **Masuk**.
- Gunakan email dan kata sandi sesuai tabel segmen [Akun Default](#akun-default-seeder).
- Peran menentukan menu yang tampil:
  - **Admin** → Dashboard, Daftar Pemesanan, Buat Pemesanan.
  - **Penyetuju** → Persetujuan, Riwayat.

### 2. Membuat Pemesanan (Admin)

1. Klik **Buat Pemesanan**.
2. Pilih tanggal **mulai** dan **selesai**.
3. Sistem memeriksa ketersediaan kendaraan & driver pada rentang tanggal tersebut.
4. Pilih kendaraan dan driver yang tersedia. 
5. Pilih penyetuju berjenjang.
6. Simpan (pemesanan akan berstatus **Menunggu Persetujuan** dan dibuatkan rantai persetujuan)

### 3. Menyetujui / Menolak (Penyetuju)

1. Pada menu **Persetujuan**, buka pemesanan yang menunggu persetujuan Anda.
2. Klik **Setujui** untuk melanjutkan ke tingkat berikutnya, atau **Tolak** dengan mengisi catatan penolakan.
3. Seluruh tingkat (rantai persetujuan) harus menyetujui agar pemesanan berstatus **Disetujui**.
4. Histori tindakan dapat dilihat pada menu **Riwayat**.

### 4. Ekspor Laporan Excel

1. Buka **Daftar Pemesanan** (admin).
2. Tentukan rentang **Dari** dan **Sampai** pada bilah ekspor.
3. Klik **Export Excel** → berkas **`.xlsx`** terunduh (`laporan-pemesanan_<dari>_<hingga>.xlsx`).
4. Laporan memuat: identitas lengkap (driver, kendaraan, admin), rentang tanggal, status, **rantai persetujuan**, dan **catatan penolakan**.