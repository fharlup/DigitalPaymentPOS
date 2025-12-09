<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Akun;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class KasirPage extends Component
{
    // Konfigurasi Midtrans saat komponen dimuat
    // public function boot()
    // {
    //     Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    //     Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    //     Config::$isSanitized = true;
    //     Config::$is3ds = true;
    //     Config::$curlOptions = [
    //     CURLOPT_SSL_VERIFYPEER => false,
    // ];
    // }

    // Mengambil data pesanan status 'pending' secara realtime
    public function getPendingTransaksisProperty()
    {
        return Transaksi::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // LOGIC 1: Proses Pembayaran Tunai (Cash)
    public function bayarTunai($transaksiId)
    {
        $this->markAsPaid($transaksiId, 'tunai');
    }

    // LOGIC 2: Proses QRIS (Request Token Midtrans)
    public function processQris($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);

        // Cek apakah sudah ada token sebelumnya biar hemat request
        if (empty($transaksi->snap_token)) {
            $params = [
                'transaction_details' => [
                    'order_id' => 'SOTO-' . $transaksi->id . '-' . time(),
                    'gross_amount' => (int) $transaksi->total_harga,
                ],
                'customer_details' => [
                    'first_name' => $transaksi->nama_pelanggan,
                ],
                'enabled_payments' => ['gopay', 'shopeepay', 'qris'],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $transaksi->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal koneksi Midtrans: ' . $e->getMessage());
                return;
            }
        }

        // Kirim perintah ke Frontend untuk buka Popup
        $this->dispatch('open-midtrans', token: $transaksi->snap_token, trx_id: $transaksi->id);
    }

    // LOGIC 3: Callback Sukses dari JS (Midtrans)
    public function paymentSuccess($transaksiId)
    {
        $this->markAsPaid($transaksiId, 'qris');
    }

    // LOGIC UTAMA: Simpan ke Database & Buat Jurnal Akuntansi
    public function markAsPaid($transaksiId, $metode)
    {
        $transaksi = Transaksi::find($transaksiId);

        // Validasi: Jangan proses jika sudah lunas
        if (!$transaksi || $transaksi->status === 'paid') return;

        DB::transaction(function () use ($transaksi, $metode) {
            // A. Update Status Transaksi
            $transaksi->update([
                'status' => 'paid',
                'metode_pembayaran' => $metode
            ]);

            // B. Buat Jurnal Otomatis (Sesuai Bab III TA)
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan (' . strtoupper($metode) . ') #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            // Tentukan Akun Debit (Tunai masuk Kas, QRIS masuk Bank)
            // Pastikan kode akun 111 (Kas) dan 112 (Bank) ada di database
            $kodeDebit = ($metode === 'qris') ? '112' : '111';
            
            $akunDebit = Akun::where('kode_akun', $kodeDebit)->first();
            $akunKredit = Akun::where('kode_akun', '411')->first(); // 411 = Penjualan

            // Fallback jika akun belum dibuat di admin (biar gak error)
            $idDebit = $akunDebit ? $akunDebit->id : 1;
            $idKredit = $akunKredit ? $akunKredit->id : 1;

            // Simpan Debit (Uang Masuk)
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $idDebit,
                'debit' => $transaksi->total_harga,
                'kredit' => 0,
            ]);

            // Simpan Kredit (Pendapatan Bertambah)
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $idKredit,
                'debit' => 0,
                'kredit' => $transaksi->total_harga,
            ]);
        });

        session()->flash('message', 'Pembayaran ' . strtoupper($metode) . ' Berhasil! Jurnal tercatat.');
    }

    public function render()
    {
        return view('livewire.kasir-page');
    }
}