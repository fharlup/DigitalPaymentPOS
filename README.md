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

biar image nya ke load

php artisan storage:link

cara jalananti test nya
seeding db nya

php artisan migrate:fresh --seed


-cek php nya dimana dulu
php --ini

nanti ada path nya misal
//C:\Users\fajar\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.ini

lalu nanti code [path nya]
;extension=pdo_sqlite

jadi
extension=pdo_sqlite

php artisan db:seed
buat seed
buat jalaini midtrans nya



buka vendor midtrans/midtrans-php/midtrasns/api requestor terus genti ke bawah

<?php

namespace Midtrans;

use Exception;

/**
 * Send request to Midtrans API
 * Better don't use this class directly, please use CoreApi, Snap, and Transaction instead
 */
class ApiRequestor
{
    /**
     * Send GET request
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function get($url, $server_key, $data_hash)
    {
        return self::remoteCall($url, $server_key, $data_hash, 'GET');
    }

    /**
     * Send POST request
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function post($url, $server_key, $data_hash)
    {
        return self::remoteCall($url, $server_key, $data_hash, 'POST');
    }

    /**
     * Send PATCH request
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function patch($url, $server_key, $data_hash)
    {
        return self::remoteCall($url, $server_key, $data_hash, 'PATCH');
    }

    /**
     * Actually send request to API server
     * @param string $url
     * @param string $server_key
     * @param mixed[] $data_hash
     * @param string $method (GET/POST/PATCH)
     * @return mixed
     * @throws Exception
     */
    public static function remoteCall($url, $server_key, $data_hash, $method)
    {
        $ch = curl_init();

        // --- DEFINISI UTAMA CURL OPTIONS (SUDAH DIPASANG ANTI-SSL) ---
        $curl_options = array(
            CURLOPT_URL => $url,
            
            // 1. MATIKAN SSL (SOLUSI LOCALHOST WINDOWS)
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT  => true,
            
            // 2. HEADER STANDAR MIDTRANS
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: midtrans-php-v2.6.2',
                'Authorization: Basic ' . base64_encode($server_key . ':')
            ),
            CURLOPT_RETURNTRANSFER => 1
        );

        // --- LOGIKA CONFIG TAMBAHAN MIDTRANS (MERGE HEADERS) ---
        if (Config::$appendNotifUrl) Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Append-Notification: ' . Config::$appendNotifUrl;
        if (Config::$overrideNotifUrl) Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'X-Override-Notification: ' . Config::$overrideNotifUrl;
        if (Config::$paymentIdempotencyKey) Config::$curlOptions[CURLOPT_HTTPHEADER][] = 'Idempotency-Key: ' . Config::$paymentIdempotencyKey;

        if (count(Config::$curlOptions)) {
            // Merge headers manually
            if (isset(Config::$curlOptions[CURLOPT_HTTPHEADER])) {
                $mergedHeaders = array_merge($curl_options[CURLOPT_HTTPHEADER], Config::$curlOptions[CURLOPT_HTTPHEADER]);
                $headerOptions = array(CURLOPT_HTTPHEADER => $mergedHeaders);
            } else {
                $mergedHeaders = array();
                $headerOptions = array(CURLOPT_HTTPHEADER => $mergedHeaders);
            }

            $curl_options = array_replace_recursive($curl_options, Config::$curlOptions, $headerOptions);
        }

        // --- ATUR METHOD & BODY (POST/PATCH) ---
        if ($method != 'GET') {
            if ($data_hash) {
                $body = json_encode($data_hash);
                $curl_options[CURLOPT_POSTFIELDS] = $body;
            } else {
                $curl_options[CURLOPT_POSTFIELDS] = '';
            }

            if ($method == 'POST') {
                $curl_options[CURLOPT_POST] = 1;
            } elseif ($method == 'PATCH') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            }
        }

        // --- EKSEKUSI ---
        curl_setopt_array($ch, $curl_options);

        // For testing purpose (Mocking)
        if (class_exists('\Midtrans\MT_Tests') && MT_Tests::$stubHttp) {
            $result = self::processStubed($curl_options, $url, $server_key, $data_hash, $method);
        } else {
            $result = curl_exec($ch);
        }

        // --- HANDLING ERROR ---
        if ($result === false) {
            // Ini akan menangkap error koneksi (seperti SSL problem)
            throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
        } 
        
        // --- DECODE JSON RESPONSE ---
        try {
            $result_array = json_decode($result);
        } catch (Exception $e) {
            throw new Exception("API Request Error unable to json_decode API response: ".$result . ' | Request url: '.$url);
        }

        // --- CEK HTTP STATUS CODE ---
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); // Tutup koneksi di sini

        if (isset($result_array->status_code) && $result_array->status_code >= 401 && $result_array->status_code != 407) {
            throw new Exception('Midtrans API Error. Status: ' . $result_array->status_code . '. Response: ' . $result, $result_array->status_code);
        } elseif ($httpCode >= 400) {
            throw new Exception('Midtrans API Error. HTTP Status: ' . $httpCode . '. Response: ' . $result, $httpCode);
        } else {
            return $result_array;
        }
    }

    private static function processStubed($curl, $url, $server_key, $data_hash, $method)
    {
        MT_Tests::$lastHttpRequest = array(
            "url" => $url,
            "server_key" => $server_key,
            "data_hash" => $data_hash,
            $method => $method,
            "curl" => $curl
        );

        return MT_Tests::$stubHttpResponse;
    }
}
