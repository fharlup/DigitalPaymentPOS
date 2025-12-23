<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Meja;
use App\Models\Akun;
use App\Models\Transaksi;       // Import Model Transaksi
use App\Models\DetailTransaksi; // Import Model Detail
use App\Models\Jurnal;          // Import Model Jurnal
use App\Models\DetailJurnal;    // Import Model Detail Jurnal
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BERSIHKAN DATA LAMA
        Schema::disableForeignKeyConstraints();
        Meja::truncate();
        Kategori::truncate();
        Produk::truncate();
        Akun::truncate();
        User::truncate();
        Transaksi::truncate();       // Reset Transaksi
        DetailTransaksi::truncate(); // Reset Detail
        Jurnal::truncate();          // Reset Jurnal
        DetailJurnal::truncate();    // Reset Detail Jurnal
        Schema::enableForeignKeyConstraints();

        // 2. BUAT USER
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

        // 3. BUAT AKUN AKUNTANSI (Master COA)
        $akunKas = Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit']);
        $akunBank = Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank BCA (QRIS)', 'tipe' => 'debit']);
        $akunPendapatan = Akun::create(['kode_akun' => '411', 'nama_akun' => 'Pendapatan Penjualan', 'tipe' => 'kredit']);

        // 4. BUAT DATA MEJA
        $dataMeja = [];
        for ($i = 1; $i <= 20; $i++) {
            $dataMeja[] = ['nomor_meja' => 'Meja ' . $i, 'created_at' => now(), 'updated_at' => now()];
        }
        $dataMeja[] = ['nomor_meja' => 'Bungkus / Take Away', 'created_at' => now(), 'updated_at' => now()];
        Meja::insert($dataMeja);

        // 5. BUAT KATEGORI
        $katMakanan = Kategori::create(['nama_kategori' => 'Makanan Berat']);
        $katCamilan = Kategori::create(['nama_kategori' => 'Camilan & Tambahan']);
        $katMinuman = Kategori::create(['nama_kategori' => 'Minuman']);

        // 6. BUAT PRODUK
        $produkList = [];

        $produkList[] = Produk::create([
            'kategori_id' => $katMakanan->id,
            'nama_produk' => 'Soto Ayam Kampung',
            'harga' => 15000,
            'stok' => 50,
            'deskripsi' => 'Soto kuah kuning bening dengan suwiran ayam kampung asli.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katMakanan->id,
            'nama_produk' => 'Soto Daging Sapi',
            'harga' => 20000,
            'stok' => 40,
            'deskripsi' => 'Potongan daging sapi empuk dengan kuah kaldu sapi asli.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katCamilan->id,
            'nama_produk' => 'Sate Telur Puyuh',
            'harga' => 3000,
            'stok' => 100,
            'deskripsi' => 'Sate telur puyuh bacem manis gurih.',
        ]);

        $produkList[] = Produk::create([
            'kategori_id' => $katMinuman->id,
            'nama_produk' => 'Es Teh Manis',
            'harga' => 4000,
            'stok' => 200,
            'deskripsi' => 'Teh melati wangi dengan gula asli.',
        ]);

        // ==========================================================
        // 7. BUAT TRANSAKSI DUMMY & JURNAL (OTOMATIS)
        // ==========================================================
        
        // Kita buat 5 transaksi contoh
        for ($i = 1; $i <= 5; $i++) {
            
            // Random metode bayar (Ganjil Tunai, Genap QRIS)
            $metode = ($i % 2 != 0) ? 'tunai' : 'qris';
            $akunDebit = ($metode == 'tunai') ? $akunKas : $akunBank;

            // A. Buat Header Transaksi
            $transaksi = Transaksi::create([
                'user_id' => 1,
                'nama_pelanggan' => 'Pelanggan Dummy ' . $i,
                'no_meja' => 'Meja ' . rand(1, 10),
                'tanggal_transaksi' => now()->subHours($i), // Mundur beberapa jam
                'total_harga' => 0, // Nanti diupdate
                'status' => 'paid', // Status Lunas (Biar masuk jurnal)
                'metode_pembayaran' => $metode,
            ]);

            // B. Buat Detail Item (Random beli 1-2 jenis produk)
            $totalBelanja = 0;
            $randomProduk = collect($produkList)->random(rand(1, 2)); 

            foreach ($randomProduk as $prod) {
                $qty = rand(1, 2);
                $subtotal = $prod->harga * $qty;
                
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $prod->id,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal
                ]);
                
                $totalBelanja += $subtotal;
            }

            // Update Total Harga di Header
            $transaksi->update(['total_harga' => $totalBelanja]);

            // C. Buat Jurnal Akuntansi Otomatis
            $jurnal = Jurnal::create([
                'transaksi_id' => $transaksi->id,
                'keterangan' => 'Penjualan ' . strtoupper($metode) . ' #' . $transaksi->id,
                'tanggal' => $transaksi->tanggal_transaksi,
            ]);

            // Debit (Uang Masuk ke Kas/Bank)
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunDebit->id,
                'debit' => $totalBelanja,
                'kredit' => 0
            ]);

            // Kredit (Pendapatan Bertambah)
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPendapatan->id,
                'debit' => 0,
                'kredit' => $totalBelanja
            ]);
        }
    }
}