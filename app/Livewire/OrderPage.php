<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Jurnal;       // Tambahan untuk Akuntansi
use App\Models\DetailJurnal; // Tambahan untuk Akuntansi
use App\Models\Akun;         // Tambahan untuk Akuntansi
use Illuminate\Support\Facades\DB;
use Midtrans\Config;         // Tambahan Midtrans
use Midtrans\Snap;           // Tambahan Midtrans

class OrderPage extends Component
{
    public $cart = [];
    public $nama_pelanggan;

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

    public function addToCart($produkId)
    {
        $produk = Produk::find($produkId);
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
            ];
        }
    }

    public function getTotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    // UPDATE: Fungsi Checkout menerima parameter metode bayar
    
public function checkout($metode = 'tunai')
    {
        if (empty($this->cart)) return;

        // Validasi nama pelanggan
        if (empty($this->nama_pelanggan)) {
            session()->flash('error', 'Mohon isi nama Anda dulu.');
            return;
        }

        $transaksi = null;

        // PERBAIKAN ADA DI BARIS BAWAH INI:
        // Tambahkan koma dan $metode di dalam kurung use (...)
        DB::transaction(function () use (&$transaksi, $metode) { 
            
            // 1. Simpan Transaksi
            $transaksi = Transaksi::create([
                'user_id' => 1, 
                'nama_pelanggan' => $this->nama_pelanggan,
                'tanggal_transaksi' => now(),
                'total_harga' => $this->getTotalProperty(),
                'status' => 'pending',
                // Sekarang $metode sudah bisa dibaca di sini
                'metode_pembayaran' => $metode === 'qris' ? 'qris' : 'tunai'
            ]);

            // 2. Simpan Detail
            foreach ($this->cart as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        });

        // == LOGIKA PERCABANGAN PEMBAYARAN ==
        
        if ($metode === 'qris') {
            $this->processMidtrans($transaksi);
        } else {
            // Jika TUNAI: Simpan ID untuk ditampilkan ke user
            $this->successOrderId = $transaksi->id;
            
            // Bersihkan keranjang
            $this->cart = [];
            $this->nama_pelanggan = '';
        }
    }
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
            'enabled_payments' => ['gopay', 'shopeepay', 'qris'], 
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $transaksi->update(['snap_token' => $snapToken]);

            // Kirim Token ke Browser Pelanggan
            $this->dispatch('trigger-payment', token: $snapToken, trx_id: $transaksi->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses QRIS: ' . $e->getMessage());
        }
    }

    // Fungsi ini dipanggil JS jika pembayaran SUKSES
    public function paymentSuccess($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        
        if ($transaksi->status == 'paid') return; // Cegah double

        DB::transaction(function () use ($transaksi) {
            // Update jadi PAID
            $transaksi->update(['status' => 'paid']);

            // === OTOMATIS JURNAL (Akuntansi) ===
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan QRIS #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            // Debit: Bank/Kas (Misal 111 Kas)
            $akunKas = Akun::where('kode_akun', '111')->first(); 
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunKas->id ?? 1,
                'debit' => $transaksi->total_harga,
                'kredit' => 0,
            ]);

            // Kredit: Penjualan (Misal 411)
            $akunJual = Akun::where('kode_akun', '411')->first();
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunJual->id ?? 2,
                'debit' => 0,
                'kredit' => $transaksi->total_harga,
            ]);
        });

        // Reset Cart setelah sukses bayar
        $this->cart = [];
        $this->nama_pelanggan = '';
        session()->flash('message', 'Pembayaran Berhasil! Pesanan sedang disiapkan.');
    }

    public function render()
    {
        return view('livewire.order-page', [
            'kategoris' => Kategori::with('produks')->get(),
        ]);
    }
}