<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Akun;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;

class KasirPage extends Component
{
    // Mengambil pesanan Pending (Belum Bayar) DAN Paid (Belum Diantar)
   // Mengambil pesanan Pending (Belum Bayar) DAN Paid (Belum Diantar)
    public function getTransaksisProperty()
    {
        return Transaksi::with(['detailTransaksi.produk']) // <--- TAMBAHAN: Tarik data relasi rincian pesanan dan nama produknya
            ->whereIn('status', ['pending', 'paid'])
            ->orderBy('created_at', 'desc') // Pesanan baru di atas
            ->get();
    } 

    // LOGIC 1: Bayar Tunai
    public function bayarTunai($transaksiId)
    {
        $this->markAsPaid($transaksiId, 'tunai');
    }

    // LOGIC 2: QRIS
    public function processQris($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);

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
                // Pastikan konfigurasi Server Key Midtrans sudah benar di .env
                \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
                \Midtrans\Config::$isSanitized = true;
                \Midtrans\Config::$is3ds = true;

                $snapToken = Snap::getSnapToken($params);
                $transaksi->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                session()->flash('error', 'Midtrans Error: ' . $e->getMessage());
                return;
            }
        }

        $this->dispatch('open-midtrans', token: $transaksi->snap_token, trx_id: $transaksi->id);
    }

    // LOGIC 3: Callback Midtrans
    public function paymentSuccess($transaksiId)
    {
        $this->markAsPaid($transaksiId, 'qris');
    }

    // LOGIC 4: Tandai Selesai (Makanan Sudah Diantar)
    public function markAsDone($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        if ($transaksi && $transaksi->status === 'paid') {
            $transaksi->update(['status' => 'done']);
            session()->flash('message', "Pesanan #{$transaksi->id} selesai!");
        }
    }

    // PRIVATE: Proses Akuntansi & Update Status ke Paid
    private function markAsPaid($transaksiId, $metode)
    {
        $transaksi = Transaksi::find($transaksiId);

        if (!$transaksi || $transaksi->status === 'paid') return;

        DB::transaction(function () use ($transaksi, $metode) {
            // Update jadi PAID (bukan done, karena harus dimasak/diantar dulu)
            $transaksi->update([
                'status' => 'paid',
                'metode_pembayaran' => $metode
            ]);

            // Buat Jurnal Otomatis
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan (' . strtoupper($metode) . ') #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            $kodeDebit = ($metode === 'qris') ? '112' : '111'; // 112: Bank, 111: Kas
            
            $akunDebit = Akun::where('kode_akun', $kodeDebit)->first();
            $akunKredit = Akun::where('kode_akun', '411')->first(); // 411: Penjualan

            $idDebit = $akunDebit ? $akunDebit->id : 1;
            $idKredit = $akunKredit ? $akunKredit->id : 1;

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $idDebit,
                'debit' => $transaksi->total_harga,
                'kredit' => 0,
            ]);

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $idKredit,
                'debit' => 0,
                'kredit' => $transaksi->total_harga,
            ]);
        });

        session()->flash('message', 'Pembayaran Berhasil! Silakan siapkan pesanan.');
    }

    public function render()
    {
        return view('livewire.kasir-page');
    }
}