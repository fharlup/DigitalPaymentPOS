<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
// use App\Models\Meja; // Aktifkan jika pakai tabel Meja
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\CoreApi;

class OrderPage extends Component
{
    // Properti Form & Keranjang
    public $nama_pelanggan;
    public $no_meja;
    public $cart = [];

    // Properti QRIS
    public $showQrisModal = false;
    public $qrisImageUrl = null; // Ini URL yang akan kita tampilkan
    public $currentTransaksiId = null;

    public function mount()
    {
        // Inisialisasi keranjang kosong jika belum ada (atau ambil dari session)
        $this->cart = [];
    }

    // Fungsi Tambah ke Keranjang (Contoh Sederhana)
    public function addToCart($produkId)
    {
        $produk = Produk::find($produkId);
        if (!$produk) return;

        // Logic sederhana tambah item
        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['qty']++;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'name' => $produk->nama_produk,
                'harga' => $produk->harga,
                'qty' => 1
            ];
        }
    }

    // === UTAMA: PROSES CHECKOUT ===
    public function checkout($metodePembayaran = 'tunai')
    {
        $this->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_meja' => 'required',
            'cart' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($metodePembayaran) {
            // 1. Hitung Total
            $totalHarga = collect($this->cart)->sum(fn($item) => $item['harga'] * $item['qty']);

            // 2. Buat Transaksi
            $transaksi = Transaksi::create([
                'nama_pelanggan' => $this->nama_pelanggan,
                'no_meja' => $this->no_meja,
                'total_harga' => $totalHarga,
                'status' => 'pending', 
                'metode_pembayaran' => $metodePembayaran
            ]);
            
            $this->currentTransaksiId = $transaksi->id;

            // 3. Simpan Detail & Kurangi Stok
            foreach ($this->cart as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga'],
                ]);
                
                // Kurangi Stok
                Produk::where('id', $item['id'])->decrement('stok', $item['qty']);
            }

            // 4. Cek Metode Pembayaran
            if ($metodePembayaran === 'qris') {
                $this->generateQris($transaksi);
            } else {
                // Tunai
                $this->resetCart();
                session()->flash('message', 'Pesanan berhasil! Silakan bayar tunai di kasir.');
            }
        });
    }

    // === LOGIC MIDTRANS CORE API ===
    public function generateQris($transaksi)
    {
        // Set Config Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => 'SOTO-' . $transaksi->id . '-' . time(),
                'gross_amount' => (int) $transaksi->total_harga,
            ],
            'qris' => [
                'acquirer' => 'gopay'
            ]
        ];

        try {
            // Request ke Core API
            $response = CoreApi::charge($params);
            
            // Cari URL Gambar di response actions
            if (isset($response->actions)) {
                foreach ($response->actions as $action) {
                    if ($action->name === 'generate-qr-code') {
                        $this->qrisImageUrl = $action->url; // Simpan URL
                        $this->showQrisModal = true; // Buka Modal
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat QRIS: ' . $e->getMessage());
        }
    }

    // === AUTO CHECK (POLLING) ===
    // Fungsi ini dipanggil otomatis oleh frontend setiap 3 detik
    public function checkPaymentStatus()
    {
        if ($this->currentTransaksiId && $this->showQrisModal) {
            $transaksi = Transaksi::find($this->currentTransaksiId);
            
            // Jika status berubah jadi 'paid' (callback masuk / kasir update)
            if ($transaksi->status === 'paid') {
                $this->showQrisModal = false;
                $this->resetCart();
                session()->flash('message', 'Pembayaran QRIS Berhasil! Pesanan diproses.');
            }
        }
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->nama_pelanggan = '';
        $this->no_meja = '';
        $this->currentTransaksiId = null;
        $this->qrisImageUrl = null;
    }

    public function render()
    {
        // Ambil produk untuk ditampilkan di menu
        $produks = Produk::where('stok', '>', 0)->get();
        return view('livewire.order-page', compact('produks'));
    }
}