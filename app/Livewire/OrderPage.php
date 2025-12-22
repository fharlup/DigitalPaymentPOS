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
use App\Models\Meja;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap; // Kita pakai Snap lagi

class OrderPage extends Component
{
    public $cart = [];
    public $nama_pelanggan;
    public $successOrderId = null; 
    public $showCartModal = false; 
    public $selectedProduct = null;
    public $showDetailModal = false;
    public $no_meja;

    // FUNGSI BUKA DETAIL
    public function openDetail($id)
    {
        $this->selectedProduct = Produk::find($id);
        $this->showDetailModal = true;
    }

    // FUNGSI TUTUP DETAIL
    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedProduct = null;
    }
    // Setup Konfigurasi Midtrans
    public function boot()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        // Fix SSL Error Localhost
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    }

    // Listener Error Midtrans
    protected $listeners = ['midtrans-error' => 'handleMidtransError'];

    public function handleMidtransError($data)
    {
        session()->flash('error', $data['message'] ?? 'Pembayaran Gagal');
    }

    // Tambah Item ke Keranjang
    public function addToCart($produkId)
    {
        $produk = Produk::find($produkId);
        
        if ($produk->stok <= 0) {
            session()->flash('error', 'Maaf, stok habis!');
            return;
        }

        if (isset($this->cart[$produkId])) {
            if ($this->cart[$produkId]['jumlah'] + 1 > $produk->stok) {
                session()->flash('error', 'Stok tidak cukup!');
                return;
            }
            $this->cart[$produkId]['jumlah']++;
            $this->cart[$produkId]['subtotal'] += $produk->harga;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'nama' => $produk->nama_produk,
                'harga' => $produk->harga,
                'jumlah' => 1,
                'subtotal' => $produk->harga,
                'gambar' => $produk->gambar,
                'max_stok' => $produk->stok
            ];
        }
    }

    // Update Qty
    public function updateQty($produkId, $change)
    {
        if (!isset($this->cart[$produkId])) return;

        $newQty = $this->cart[$produkId]['jumlah'] + $change;
        $maxStok = $this->cart[$produkId]['max_stok'];

        if ($newQty <= 0) {
            unset($this->cart[$produkId]);
            if (empty($this->cart)) $this->showCartModal = false;
            return;
        }

        if ($change > 0 && $newQty > $maxStok) {
             session()->flash('error', 'Stok maksimal tercapai!');
             return;
        }

        $this->cart[$produkId]['jumlah'] = $newQty;
        $this->cart[$produkId]['subtotal'] = $newQty * $this->cart[$produkId]['harga'];
    }

    public function getTotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    // Proses Checkout
    public function checkout($metode = 'tunai')
    {
        if (empty($this->cart)) return;

        // ============================================================
        // 1. VALIDASI MANUAL: NAMA & MEJA WAJIB ISI
        // ============================================================
        if (empty($this->nama_pelanggan) || empty($this->no_meja)) {
            
            // A. Tutup modal agar user lihat form input
            $this->showCartModal = false; 

            // B. Notif Merah
            session()->flash('error', 'Nama dan Nomor Meja wajib diisi!');

            // C. Validasi Merah di Inputan
            $this->validate([
                'nama_pelanggan' => 'required|min:2',
                'no_meja' => 'required',
            ], [
                'nama_pelanggan.required' => 'Siapa nama kamu?',
                'no_meja.required' => 'Kamu duduk di meja berapa?',
            ]);

            return; // STOP
        }
        // ============================================================

        DB::transaction(function () use ($metode) {
            $transaksi = Transaksi::create([
                'user_id' => 1, 
                'nama_pelanggan' => $this->nama_pelanggan,
                'no_meja' => $this->no_meja, // <--- SIMPAN NO MEJA
                'tanggal_transaksi' => now(),
                'total_harga' => $this->getTotalProperty(),
                'status' => 'pending', 
                'metode_pembayaran' => $metode === 'qris' ? 'qris' : 'tunai'
            ]);

            foreach ($this->cart as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                ]);

                $produk = Produk::find($item['id']);
                if ($produk) $produk->decrement('stok', $item['jumlah']);
            }

            if ($metode === 'qris') {
                $this->processMidtrans($transaksi);
            } else {
                $this->successOrderId = $transaksi->id;
                $this->cart = [];
                $this->showCartModal = false;
                session()->flash('message', 'Pesanan Meja ' . $this->no_meja . ' Berhasil Dibuat!');
            }
        });
        
        if ($metode !== 'qris') {
            // Reset form setelah sukses tunai
            $this->nama_pelanggan = '';
            $this->no_meja = ''; 
        }
    
    }

    // --- KEMBALI KE SNAP ---
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
        ];

        try {
            // Minta Token Snap
            $snapToken = Snap::getSnapToken($params);
            
            $transaksi->update(['snap_token' => $snapToken]);

            // Kirim Token ke Frontend
            $this->dispatch('trigger-payment', token: $snapToken, trx_id: $transaksi->id);

        } catch (\Exception $e) {
            // 2. LOGIC TRACKING ERROR (Masuk ke storage/logs/laravel.log)
            Log::error('GAGAL MIDTRANS untuk Transaksi ID: ' . $transaksi->id);
            Log::error('Pesan Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' baris ' . $e->getLine());
            
            // Tampilkan pesan ringkas ke user
            session()->flash('error', 'Gagal koneksi: ' . $e->getMessage());
        }
    }
    // Callback Sukses
    public function paymentSuccess($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        if ($transaksi->status == 'paid') return;

        DB::transaction(function () use ($transaksi) {
            $transaksi->update(['status' => 'paid']);

            // Jurnal Otomatis
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan Snap QRIS #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            $akunBank = Akun::where('kode_akun', '112')->first() ?? Akun::find(1);
            $akunJual = Akun::where('kode_akun', '411')->first() ?? Akun::find(2);

            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunBank->id, 'debit' => $transaksi->total_harga, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunJual->id, 'debit' => 0, 'kredit' => $transaksi->total_harga]);
        });

        $this->cart = [];
        $this->nama_pelanggan = '';
        $this->showCartModal = false;
        session()->flash('message', 'Pembayaran Berhasil!');
    }

    public function render()
    {
        return view('livewire.order-page', [
            'kategoris' => Kategori::with('produks')->get(),
            'mejas' => Meja::all()
        ]);
    }
}