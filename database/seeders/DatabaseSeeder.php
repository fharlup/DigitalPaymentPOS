<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Meja;
use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. BERSIHKAN DATA LAMA (RESET TOTAL)
        // ==========================================
        Schema::disableForeignKeyConstraints();
        DetailJurnal::truncate();
        Jurnal::truncate();
        DetailTransaksi::truncate();
        Transaksi::truncate();
        Produk::truncate();
        Kategori::truncate();
        Meja::truncate();
        Akun::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // ==========================================
        // 2. BUAT USER ADMIN & KASIR
        // ==========================================
        User::factory()->create([
            'name' => 'Mbak Eni',
            'email' => 'admin@soto.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Kasir 1',
            'email' => 'kasir@soto.com',
            'role' => 'kasir',
            'password' => bcrypt('password'),
        ]);

        // ==========================================
        // 3. BUAT AKUN AKUNTANSI (MASTER COA)
        // ==========================================
        $akunKas = Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit']);
        $akunBank = Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank BCA (QRIS)', 'tipe' => 'debit']);
        $akunPendapatan = Akun::create(['kode_akun' => '411', 'nama_akun' => 'Pendapatan Penjualan', 'tipe' => 'kredit']);

        // ==========================================
        // 4. BUAT DATA MEJA
        // ==========================================
        $dataMeja = [];
        for ($i = 1; $i <= 20; $i++) {
            $dataMeja[] = ['nomor_meja' => 'Meja ' . $i, 'created_at' => now(), 'updated_at' => now()];
        }
        $dataMeja[] = ['nomor_meja' => 'Bungkus / Take Away', 'created_at' => now(), 'updated_at' => now()];
        Meja::insert($dataMeja);

        // ==========================================
        // 5. BUAT KATEGORI
        // ==========================================
        $katMakanan = Kategori::create(['nama_kategori' => 'Makanan Berat']);
        $katCamilan = Kategori::create(['nama_kategori' => 'Camilan & Tambahan']);
        $katMinuman = Kategori::create(['nama_kategori' => 'Minuman']);

        // ==========================================
        // 6. BUAT PRODUK (LENGKAP DENGAN DESKRIPSI)
        // ==========================================
        $produkList = [];

        $produkList[] = Produk::create([
            'kategori_id' => $katMakanan->id,
            'nama_produk' => 'Soto Ayam Kampung',
            'harga' => 15000,
            'stok' => 50,
            'deskripsi' => 'Kuah kuning bening kaya rempah dengan suwiran ayam kampung asli yang gurih.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katMakanan->id,
            'nama_produk' => 'Soto Daging Sapi',
            'harga' => 20000,
            'stok' => 40,
            'deskripsi' => 'Potongan daging sapi tenderloin yang empuk dengan kuah kaldu sapi asli yang nendang.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katCamilan->id,
            'nama_produk' => 'Sate Telur Puyuh',
            'harga' => 3000,
            'stok' => 100,
            'deskripsi' => 'Sate telur puyuh bacem dengan bumbu manis gurih yang meresap sempurna.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katCamilan->id,
            'nama_produk' => 'Perkedel Kentang',
            'harga' => 2000,
            'stok' => 50,
            'deskripsi' => 'Perkedel kentang lembut dengan bumbu lada dan bawang goreng.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katMinuman->id,
            'nama_produk' => 'Es Teh Manis',
            'harga' => 4000,
            'stok' => 200,
            'deskripsi' => 'Teh melati wangi diseduh segar dengan gula asli dan es batu kristal.',
        ]);
        
        $produkList[] = Produk::create([
            'kategori_id' => $katMinuman->id,
            'nama_produk' => 'Es Jeruk Peras',
            'harga' => 6000,
            'stok' => 50,
            'deskripsi' => 'Jeruk peras asli (bukan sirup) yang kaya vitamin C, segar banget!',
        ]);

        // ==========================================
        // 7. GENERATE TRANSAKSI & JURNAL DUMMY
        // ==========================================
        // Kita buat 10 transaksi acak biar laporannya ramai
        
        for ($i = 1; $i <= 10; $i++) {
            
            // Random: Metode Bayar (Ganjil=Tunai, Genap=QRIS)
            $metode = ($i % 2 != 0) ? 'tunai' : 'qris';
            
            // Random: Tanggal Transaksi (Mundur 0-7 hari ke belakang)
            $tanggal = now()->subDays(rand(0, 7))->subHours(rand(1, 12));

            // A. Header Transaksi
            $transaksi = Transaksi::create([
                'user_id' => 1,
                'nama_pelanggan' => 'Pelanggan ' . $i,
                'no_meja' => 'Meja ' . rand(1, 15),
                'tanggal_transaksi' => $tanggal,
                'total_harga' => 0, // Hitung nanti
                'status' => 'paid', // Kita set LUNAS biar masuk laporan
                'metode_pembayaran' => $metode,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // B. Detail Item (Beli 1 sampai 3 jenis barang acak)
            $totalBelanja = 0;
            $randomItems = collect($produkList)->random(rand(1, 3)); 

            foreach ($randomItems as $prod) {
                $qty = rand(1, 2);
                $subtotal = $prod->harga * $qty;
                
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $prod->id,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
                
                $totalBelanja += $subtotal;
            }

            // Update Total Harga di Header
            $transaksi->update(['total_harga' => $totalBelanja]);

            // C. JURNAL AKUNTANSI OTOMATIS
            // Tentukan Debit kemana? (Tunai -> Kas, QRIS -> Bank)
            $akunDebit = ($metode == 'tunai') ? $akunKas : $akunBank;

            $jurnal = Jurnal::create([
                'transaksi_id' => $transaksi->id,
                'keterangan' => 'Penjualan ' . strtoupper($metode) . ' #' . $transaksi->id,
                'tanggal' => $tanggal,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // Debit: Uang Masuk
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunDebit->id,
                'debit' => $totalBelanja,
                'kredit' => 0,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // Kredit: Pendapatan
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPendapatan->id,
                'debit' => 0,
                'kredit' => $totalBelanja,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);
        }
    }
}