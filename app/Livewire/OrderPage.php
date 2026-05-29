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
use Midtrans\Snap;

class OrderPage extends Component
{
    public $cart = [];
    public $nama_pelanggan;
    public $no_meja; 
    public $showCartModal = false; 
    public $selectedProduct = null;
    public $showDetailModal = false;

    // Menyimpan daftar transaksi yang sedang aktif (belum selesai/done)
    public $activeTransactions = []; 

    protected $listeners = ['midtrans-error' => 'handleMidtransError'];

    public function mount()
    {
        $this->loadActiveTransactions();
    }

    // --- 1. LOAD TRANSAKSI (Logic agar Pelanggan bisa memantau status) ---
 // --- 1. LOAD TRANSAKSI ---
   public function loadActiveTransactions()
    {
        $trxIds = session()->get('active_trx_ids', []);
        
        if (!empty($trxIds)) {
            $this->activeTransactions = Transaksi::whereIn('id', $trxIds)
                ->orderBy('created_at', 'desc') // Pastikan tidak ada ->where('status', '!=', 'done')
                ->get();
        } else {
            $this->activeTransactions = [];
        }
    } 

    // --- 2. POLLING (Dipanggil tiap 3 detik oleh wire:poll di blade) ---
    public function refreshStatus()
    {
        $this->loadActiveTransactions();
    }

    // --- 3. LOGIC CHECKOUT (Simpan ID ke Session) ---
    public function checkout($metode = 'tunai')
    {
        if (empty($this->cart)) return;

        $this->validate([
            'nama_pelanggan' => 'required',
            'no_meja' => 'required'
        ]);

        DB::transaction(function () use ($metode) {
            // A. Buat Transaksi
            $transaksi = Transaksi::create([
                'user_id' => 1, // Pastikan ada user ID 1 (Admin/System) di DB
                'nama_pelanggan' => $this->nama_pelanggan,
                'no_meja' => $this->no_meja,
                'tanggal_transaksi' => now(),
                'total_harga' => $this->getTotalProperty(),
                'status' => 'pending', 
                'metode_pembayaran' => $metode === 'qris' ? 'qris' : 'tunai'
            ]);

            // B. Simpan Detail
            foreach ($this->cart as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Kurangi Stok
                Produk::where('id', $item['id'])->decrement('stok', $item['jumlah']);
            }
            
            // C. PENTING: Simpan ID Transaksi ke Session Pelanggan
            // Agar pelanggan bisa memantau status pesanan ini nanti
            $trxIds = session()->get('active_trx_ids', []); 
            $trxIds[] = $transaksi->id; 
            session()->put('active_trx_ids', $trxIds); 

            // D. Refresh State Livewire
            $this->loadActiveTransactions(); 
            $this->cart = [];
            $this->showCartModal = false;

            // E. Handle Midtrans (Jika QRIS)
            if ($metode === 'qris') {
                $this->processMidtrans($transaksi);
            } else {
                session()->flash('message', 'Pesanan Dibuat! Mohon bayar di Kasir.');
            }
        });
    }

    // --- 4. MIDTRANS LOGIC ---
    public function processMidtrans($transaksi)
    {
        // Config Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [ 
                'order_id' => 'SOTO-' . $transaksi->id . '-' . time(), 
                'gross_amount' => (int) $transaksi->total_harga 
            ],
            'customer_details' => [ 'first_name' => $transaksi->nama_pelanggan ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $transaksi->update(['snap_token' => $snapToken]);
            
            // Kirim event ke Frontend untuk buka Popup
            $this->dispatch('trigger-payment', token: $snapToken, trx_id: $transaksi->id);
        } catch (\Exception $e) { 
            session()->flash('error', 'Gagal koneksi payment gateway'); 
        }
    }

    // --- 5. CALLBACK SUKSES (Dari Popup Midtrans) ---
    public function paymentSuccess($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        if (!$transaksi || $transaksi->status == 'paid') return;

        DB::transaction(function () use ($transaksi) {
            // Update status jadi PAID
            $transaksi->update(['status' => 'paid']);
            
            // Buat Jurnal Otomatis
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan QRIS #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            // Pastikan akun ID tersedia (Gunakan fallback id jika null)
            $akunBank = Akun::where('kode_akun', '112')->first();
            $akunJual = Akun::where('kode_akun', '411')->first();

            $bankId = $akunBank ? $akunBank->id : 1;
            $jualId = $akunJual ? $akunJual->id : 1;

            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $bankId, 'debit' => $transaksi->total_harga, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $jualId, 'debit' => 0, 'kredit' => $transaksi->total_harga]);
        });

        $this->loadActiveTransactions(); 
        session()->flash('message', 'Pembayaran Berhasil! Pesanan sedang disiapkan.');
    }

    public function handleMidtransError($data)
    {
        session()->flash('error', $data['message'] ?? 'Pembayaran Gagal');
    }

    // --- FUNGSI PENDUKUNG LAINNYA ---
    public function closeTransaction($id)
    {
        $trxIds = session()->get('active_trx_ids', []);
        $updatedIds = array_diff($trxIds, [$id]);
        session()->put('active_trx_ids', $updatedIds);
        $this->loadActiveTransactions();
    }

    public function openDetail($id) { $this->selectedProduct = Produk::find($id); $this->showDetailModal = true; }
    public function closeDetail() { $this->showDetailModal = false; $this->selectedProduct = null; }
    
    public function addToCart($produkId) {
        $produk = Produk::find($produkId);
        if ($produk->stok <= 0) { session()->flash('error', 'Stok habis!'); return; }
        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['jumlah']++;
            $this->cart[$produkId]['subtotal'] += $produk->harga;
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id, 'nama' => $produk->nama_produk, 'harga' => $produk->harga,
                'jumlah' => 1, 'subtotal' => $produk->harga, 'gambar' => $produk->gambar, 'max_stok' => $produk->stok
            ];
        }
    }

    public function updateQty($produkId, $change) {
        if (!isset($this->cart[$produkId])) return;
        $newQty = $this->cart[$produkId]['jumlah'] + $change;
        if ($newQty <= 0) { unset($this->cart[$produkId]); if (empty($this->cart)) $this->showCartModal = false; return; }
        // Cek stok max
        if ($newQty > $this->cart[$produkId]['max_stok']) return;
        
        $this->cart[$produkId]['jumlah'] = $newQty;
        $this->cart[$produkId]['subtotal'] = $newQty * $this->cart[$produkId]['harga'];
    }

    public function getTotalProperty() { return array_sum(array_column($this->cart, 'subtotal')); }

    public function render()
    {
        return view('livewire.order-page', [
            'kategoris' => Kategori::with('produks')->get(),
            'mejas' => Meja::all(),
        ]);
    }
}