<?php

namespace App\Observers;

use App\Models\Transaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Akun;

class TransaksiObserver
{
    public function updated(Transaksi $transaksi): void
    {
        // Cek jika status berubah menjadi 'paid'
        if ($transaksi->isDirty('status') && $transaksi->status === 'paid') {
            
            // 1. Buat Header Jurnal
            $jurnal = Jurnal::create([
                'transaksi_id' => $transaksi->id,
                'keterangan'   => 'Penjualan Tunai #' . $transaksi->id,
                'tanggal'      => now(),
            ]);

            // Ambil ID Akun (Pastikan data ini ada di Seeder/Test)
            // Menggunakan first() untuk simplifikasi, idealnya pakai where code '111'
            $akunKas  = Akun::where('nama_akun', 'Kas Tunai')->first(); 
            $akunJual = Akun::where('nama_akun', 'Penjualan')->first();

            if ($akunKas && $akunJual) {
                // 2. Debit: Kas
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $akunKas->id,
                    'debit'     => $transaksi->total_harga,
                    'kredit'    => 0,
                ]);

                // 3. Kredit: Penjualan
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $akunJual->id,
                    'debit'     => 0,
                    'kredit'    => $transaksi->total_harga,
                ]);
            }
        }
    }
}