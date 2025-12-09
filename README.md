📘 README – Project POS "Soto Mbak Eni" (Laravel + Filament + Livewire)

Panduan lengkap untuk menjalankan project Tugas Akhir Aplikasi POS Soto Mbak Eni yang dibangun menggunakan Laravel 10, Filament V3, dan Livewire.

🚀 1. Persiapan Software (Wajib Install)

Pastikan perangkatmu sudah terpasang software berikut:

XAMPP (PHP 8.1 atau lebih baru)

Composer – https://getcomposer.org/

Visual Studio Code

Git 



🛠️ 2. Langkah Setup Project

A. Jika file diberikan dalam bentuk ZIP/Flashdisk

Extract project ke lokasi pilihan, contoh:

C:\ngoding\soto-mbak-eni

Buka folder project di VS Code.

Buka Terminal di VS Code dengan menekan Ctrl + J.

B. Install Library Project

Jalankan perintah berikut untuk mendownload folder vendor:

composer install

C. Setting Environment (.env)

Buat file .env dari contoh:

copy .env.example .env

(Mac/Linux gunakan cp .env.example .env)

Generate application key:

php artisan key:generate

D. Setup Database


Jalankan Apache dan MySQL di XAMPP.

Buka browser: http://localhost/phpmyadmin

Buat database baru bernama:

db_soto_mbak_eni

Buka file .env dan pastikan setting berikut:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_soto_mbak_eni
DB_USERNAME=root
DB_PASSWORD=

E. Migrasi Database

Jalankan perintah berikut untuk membuat seluruh tabel yang dibutuhkan:

php artisan migrate

👤 3. Membuat Akun Admin & Data Awal

A. Buat User Super Admin Filament

php artisan make:filament-user

Isi seperti berikut:

Name: Admin

Email: admin@soto.com

Password: bebas

B. (Opsional) Isi Data Dummy

Jika ingin data COA awal seperti Akun Kas dan Penjualan, bisa input manual via Admin Panel atau import SQL jika tersedia.

▶️ 4. Menjalankan Aplikasi

Jalankan server Laravel dengan:

php artisan serve

Akses Halaman Aplikasi

Admin Panel (Penjualan & Akuntansi)
👉 http://127.0.0.1:8000/admin

Halaman Pelanggan (Pesan Makanan)
👉 http://127.0.0.1:8000/pesan

Halaman Kasir (Pembayaran)
👉 http://127.0.0.1:8000/kasir

Login menggunakan akun admin yang dibuat pada langkah sebelumnya.

✅ Selesai!

Project sekarang sudah siap digunakan. Jika ada error, pastikan:

XAMPP sudah menyala

.env sudah benar

Composer sudah terinstall

Migrasi sudah dijalankan


