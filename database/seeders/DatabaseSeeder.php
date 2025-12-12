<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Akun;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT USER ADMIN & KASIR
        User::create([
            'name' => 'Juragan Soto',
            'email' => 'admin@soto.com',
            'password' => Hash::make('password'), // Password: password
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Mbak Kasir',
            'email' => 'kasir@soto.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // 2. BUAT AKUN AKUNTANSI (COA)
        Akun::insert([
            ['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit'],
            ['kode_akun' => '112', 'nama_akun' => 'Bank BCA', 'tipe' => 'debit'],
            ['kode_akun' => '411', 'nama_akun' => 'Penjualan Makanan', 'tipe' => 'kredit'],
        ]);

        // 3. BUAT KATEGORI
        $katMakanan = Kategori::create(['nama_kategori' => 'Makanan Berat']);
        $katCamilan = Kategori::create(['nama_kategori' => 'Camilan & Tambahan']);
        $katMinuman = Kategori::create(['nama_kategori' => 'Minuman Segar']);

        // 4. BUAT PRODUK (MENU) - Total 8 Menu biar Grid 2 Kolom Kelihatan Bagus
        $produks = [
            [
                'kategori_id' => $katMakanan->id,
                'nama_produk' => 'Soto Ayam Kampung',
                'harga' => 15000,
                'stok' => 50,
                'gambar' => null, // Biarkan null biar muncul icon 🍲
            ],
            [
                'kategori_id' => $katMakanan->id,
                'nama_produk' => 'Soto Daging Sapi',
                'harga' => 20000,
                'stok' => 40,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katMakanan->id,
                'nama_produk' => 'Nasi Putih',
                'harga' => 5000,
                'stok' => 100,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katCamilan->id,
                'nama_produk' => 'Perkedel Kentang',
                'harga' => 3000,
                'stok' => 50,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katCamilan->id,
                'nama_produk' => 'Sate Telur Puyuh',
                'harga' => 5000,
                'stok' => 30,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katCamilan->id,
                'nama_produk' => 'Kerupuk Kaleng',
                'harga' => 2000,
                'stok' => 100,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katMinuman->id,
                'nama_produk' => 'Es Jeruk Peras',
                'harga' => 7000,
                'stok' => 50,
                'gambar' => null,
            ],
            [
                'kategori_id' => $katMinuman->id,
                'nama_produk' => 'Teh Manis Hangat',
                'harga' => 4000,
                'stok' => 100,
                'gambar' => null,
            ],
        ];

        foreach ($produks as $p) {
            Produk::create($p);
        }

        // 5. BUAT TRANSAKSI PALSU (7 HARI TERAKHIR) - Biar Grafik Dashboard Naik Turun
        $allProduk = Produk::all();
        
        // Loop mundur 7 hari ke belakang
        for ($i = 6; $i >= 0; $i--) {
            // Random jumlah transaksi per hari (antara 2 s/d 8 transaksi)
            $jumlahTransaksi = rand(2, 8);
            
            for ($j = 0; $j < $jumlahTransaksi; $j++) {
                $tanggal = Carbon::now()->subDays($i)->setTime(rand(8, 20), rand(0, 59));
                
                // Ambil produk acak
                $produkAcak = $allProduk->random(rand(1, 3)); 
                $totalHarga = 0;
                
                // Buat Transaksi Header
                $transaksi = Transaksi::create([
                    'user_id' => 1,
                    'nama_pelanggan' => 'Pelanggan ' . rand(1, 100),
                    'tanggal_transaksi' => $tanggal,
                    'total_harga' => 0, // Nanti diupdate
                    'status' => 'paid', // Langsung lunas biar masuk grafik income
                    'metode_pembayaran' => rand(0, 1) ? 'tunai' : 'qris',
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                // Buat Detail Item
                foreach ($produkAcak as $item) {
                    $qty = rand(1, 2);
                    $subtotal = $item->harga * $qty;
                    $totalHarga += $subtotal;

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $item->id,
                        'jumlah' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ]);
                }

                // Update Total Harga Header
                $transaksi->update(['total_harga' => $totalHarga]);
            }
        }
    }
}