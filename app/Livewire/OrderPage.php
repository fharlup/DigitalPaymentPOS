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
use Midtrans\Snap;

class OrderPage extends Component
{
    public $cart = [];
    public $nama_pelanggan;
    public $no_meja; 
    public $showCartModal = false; 
    public $selectedProduct = null;
    public $showDetailModal = false;

    // --- PERUBAHAN DI SINI (JADI ARRAY) ---
    public $activeTransactions = []; 

    protected $listeners = ['midtrans-error' => 'handleMidtransError'];

    public function boot()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        Config::$curlOptions = [CURLOPT_SSL_VERIFYPEER => false];
    }

    public function mount()
    {
        $this->loadActiveTransactions();
    }

    // --- FUNGSI LOAD TRANSAKSI DARI SESSION ---
    public function loadActiveTransactions()
    {
        // Ambil array ID dari session, defaultnya kosong
        $trxIds = session()->get('active_trx_ids', []);
        
        if (!empty($trxIds)) {
            // Ambil semua transaksi yang ID-nya ada di session
            $this->activeTransactions = Transaksi::whereIn('id', $trxIds)
                ->orderBy('created_at', 'desc') // Yang baru di atas
                ->get();
        } else {
            $this->activeTransactions = [];
        }
    }

    // --- FUNGSI HAPUS (CLOSE) KARTU STATUS ---
    public function closeTransaction($id)
    {
        $trxIds = session()->get('active_trx_ids', []);
        
        // Hapus ID yang dipilih dari array
        $updatedIds = array_diff($trxIds, [$id]);
        
        // Simpan balik ke session
        session()->put('active_trx_ids', $updatedIds);
        
        // Reload tampilan
        $this->loadActiveTransactions();
    }

    // --- FUNGSI POLLING (AUTO UPDATE STATUS) ---
    public function refreshStatus()
    {
        // Cek database ulang untuk semua transaksi aktif
        $this->loadActiveTransactions();
    }

    // ... (Fungsi Keranjang, Detail, Qty TETAP SAMA seperti sebelumnya) ...
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
        $this->cart[$produkId]['jumlah'] = $newQty;
        $this->cart[$produkId]['subtotal'] = $newQty * $this->cart[$produkId]['harga'];
    }
    public function getTotalProperty() { return array_sum(array_column($this->cart, 'subtotal')); }


    // --- LOGIKA CHECKOUT (UPDATE SESSION ARRAY) ---
    public function checkout($metode = 'tunai')
    {
        if (empty($this->cart)) return;

        if (empty($this->nama_pelanggan) || empty($this->no_meja)) {
            $this->showCartModal = false; 
            session()->flash('error', 'Mohon lengkapi Nama dan Meja!');
            $this->validate([ 'nama_pelanggan' => 'required', 'no_meja' => 'required' ]);
            return;
        }

        DB::transaction(function () use ($metode) {
            $transaksi = Transaksi::create([
                'user_id' => 1, 
                'nama_pelanggan' => $this->nama_pelanggan,
                'no_meja' => $this->no_meja,
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
            
            // --- UPDATE SESSION ARRAY ---
            $trxIds = session()->get('active_trx_ids', []); // Ambil array lama
            $trxIds[] = $transaksi->id; // Tambah ID baru
            session()->put('active_trx_ids', $trxIds); // Simpan lagi

            $this->loadActiveTransactions(); // Refresh
            
            $this->cart = [];
            $this->showCartModal = false;

            if ($metode === 'qris') {
                $this->processMidtrans($transaksi);
            }
            //s
        });
    }

    public function processMidatrans($transaksi)
    {
        $params = [
            'transaction_details' => [ 'order_id' => 'SOTO-' . $transaksi->id . '-' . time(), 'gross_amount' => (int) $transaksi->total_harga ],
            'customer_details' => [ 'first_name' => $transaksi->nama_pelanggan ],
        ];
        try {
            $snapToken = Snap::getSnapToken($params);
            $transaksi->update(['snap_token' => $snapToken]);
            $this->dispatch('trigger-payment', token: $snapToken, trx_id: $transaksi->id);
        } catch (\Exception $e) { session()->flash('error', 'Gagal koneksi payment'); }
    }

    public function handleMidtransError($data)
    {
        session()->flash('error', $data['message'] ?? 'Pembayaran Gagal');
    }

    public function paymentSuccess($transaksiId)
    {
        $transaksi = Transaksi::find($transaksiId);
        if (!$transaksi || $transaksi->status == 'paid') return;

        DB::transaction(function () use ($transaksi) {
            $transaksi->update(['status' => 'paid']);
            
            $jurnal = Jurnal::create([
                'tanggal' => now(),
                'keterangan' => 'Penjualan QRIS #' . $transaksi->id,
                'transaksi_id' => $transaksi->id,
            ]);

            $akunBank = Akun::where('kode_akun', '112')->first() ?? Akun::find(1);
            $akunJual = Akun::where('kode_akun', '411')->first() ?? Akun::find(2);

            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunBank->id ?? 1, 'debit' => $transaksi->total_harga, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunJual->id ?? 2, 'debit' => 0, 'kredit' => $transaksi->total_harga]);
        });

        $this->loadActiveTransactions(); // Refresh tampilan
        session()->flash('message', 'Pembayaran Berhasil!');
    }

    public function render()
    {
        return view('livewire.order-page', [
            'kategoris' => Kategori::with('produks')->get(),
            'mejas' => Meja::all(),
        ]);
    }
}