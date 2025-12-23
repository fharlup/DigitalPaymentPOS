<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config; // <--- Pastikan ini ada
use App\Observers\TransaksiObserver;
use App\Models\Transaksi;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Konfigurasi Global Midtrans
        Transaksi::observe(TransaksiObserver::class);
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 2. OBAT KUAT: Matikan Verifikasi SSL Global
        // Ini memaksa PHP untuk "tutup mata" soal sertifikat keamanan
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT  => true, // Paksa koneksi baru
        ];
    }
}