<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Akun;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class OrderPage extends Component
{
    public $cart = [];
    public $nama_pelanggan;
    public $successOrderId = null; // Menyimpan ID order jika bayar tunai

    // Setup Konfigurasi Midtrans
    public function boot()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        // Fix SSL Error Localhost (Opsional jika sudah di AppServiceProvider, tapi aman ditaruh sini juga)
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    }

    // Menambah item ke keranjang
    public function addToCart($produkId)
    {
        $produk = Produk::find($produkId);
        
        // Cek stok dulu
        if ($produk->stok <= 0) {
            session()->flash('error', 'Maaf, stok habis!');
            return;
        }

        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['jumlah']++;
            $this->cart[$produkId]['subtotal'] += $produk->harga;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'nama' => $produk->nama_produk,
                'harga' => $produk->harga,
                'jumlah' => 1,
                'subtotal' => $produk->harga,
                'gambar' => $produk->gambar, // Simpan path gambar buat di keranjang (opsional)
            ];
        }
    }

    // Hitung Total
    public function getTotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    // Proses Checkout Utama
    public function checkout($metode = 'tunai')
    {
        if (empty($this->cart)) return;

        if (empty($this->nama_pelanggan)) {
            session()->flash('error', 'Mohon isi nama Anda dulu.');
            return;
        }

        DB::transaction(function () use ($metode) {
            // 1. Buat Transaksi (Status Awal: Pending)
            $transaksi = Transaksi::create([
                'user_id' => 1, // User default system
                'nama_pelanggan' => $this->nama_pelanggan,
                'tanggal_transaksi' => now(),
                'total_harga' => $this->getTotalProperty(),
                'status' => 'pending', 
                'metode_pembayaran' => $metode === 'qris' ? 'qris' : 'tunai'
            ]);

            // 2. Simpan Detail Item
            foreach ($this->cart as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 3. Logika Percabangan
            if ($metode === 'qris') {
                // Jika QRIS -> Panggil Midtrans
                $this->processMidtrans($transaksi);
            } else {
                // Jika Tunai -> Tampilkan Nomor Order & Selesai
                $this->successOrderId = $transaksi->id;
                $this->cart = [];
                $this->nama_pelanggan = '';
            }
        });
    }

    // Request Token ke Midtrans (Khusus QRIS)
    public function processMidtrans($transaksi)
    {
        $params = [
            'transaction_details' => [
                'order_id' => 'SOTO-' . $transaksi->id . '-' . time(),
                'gross_amount' => (int) $transaksi->total_harga,
            ],
            'customer_details' => [
                'first_name' => $transaksi->nama_pelanggan,
            ],
            // Kita hapus enabled_payments biar muncul semua opsi di Simulator
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $transaksi->update(['snap_token' => $snapToken]);

            // Kirim token ke frontend
            $this->dispatch('trigger-payment', token: $snapToken, trx_id: $transaksi->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Midtrans Error: ' . $e->getMessage());
        }
    }

    // Callback Sukses (Hanya dipanggil jika QRIS Berhasil dibayar)
    public function paymentSuccess($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        if ($transaksi->status == 'paid') return;

        DB::transaction(function () use ($transaksi) {
            // Update Status
            $transaksi->update(['status' => 'paid']);

            // === JURNAL OTOMATIS (QRIS) ===
            // Debit: Bank (112), Kredit: Penjualan (411)
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan QRIS #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            $akunBank = Akun::where('kode_akun', '112')->first() ?? Akun::find(1);
            $akunJual = Akun::where('kode_akun', '411')->first() ?? Akun::find(2);

            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunBank->id, 'debit' => $transaksi->total_harga, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunJual->id, 'debit' => 0, 'kredit' => $transaksi->total_harga]);
        });

        $this->cart = [];
        $this->nama_pelanggan = '';
        session()->flash('message', 'Pembayaran QRIS Berhasil! Silakan ambil pesanan.');
    }

    public function render()
    {
        return view('livewire.order-page', [
            'kategoris' => Kategori::with('produks')->get(),
        ]);
    }
}