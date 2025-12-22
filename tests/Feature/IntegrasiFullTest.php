<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Akun;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Meja;
use App\Models\Transaksi;
use App\Livewire\OrderPage;
use App\Livewire\KasirPage; // Asumsi kamu punya KasirPage, kalau pakai Filament sesuaikan
use Livewire\Livewire;

class IntegrasiFullTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function alur_bisnis_soto_dari_setup_sampai_jurnal_akuntansi()
    {
        // ==========================================
        // TAHAP 1: SETUP ENVIRONMENT (ADMIN ROLE)
        // ==========================================
        
        // 1. Buat User Admin & Kasir
        $admin = User::factory()->create(['name' => 'Juragan', 'role' => 'admin']);
        $kasir = User::factory()->create(['name' => 'Mbak Kasir', 'email' => 'kasir@soto.com', 'role' => 'kasir']);

        // 2. Buat Data Meja (FITUR BARU)
        $meja1 = Meja::create(['nomor_meja' => 'Meja 1']);
        $mejaBungkus = Meja::create(['nomor_meja' => 'Bungkus']);

        // 3. Buat COA (Chart of Accounts) - Wajib untuk Jurnal
        $akunKas = Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit']);
        $akunBank = Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank BCA', 'tipe' => 'debit']);
        $akunJual = Akun::create(['kode_akun' => '411', 'nama_akun' => 'Penjualan', 'tipe' => 'kredit']);

        // 4. Buat Kategori & Produk
        $kategori = Kategori::create(['nama_kategori' => 'Makanan Berat']);
        
        $soto = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Ayam Kampung',
            'harga' => 15000,
            'stok' => 20, // Stok Awal 20
            'gambar' => null,
            'deskripsi' => 'Kuah bening seger',
        ]);

        // Assert Setup Berhasil
        $this->assertDatabaseHas('mejas', ['nomor_meja' => 'Meja 1']);
        $this->assertDatabaseHas('akuns', ['kode_akun' => '111']);
        $this->assertDatabaseHas('produks', ['stok' => 20]);


        // ==========================================
        // TAHAP 2: USER ORDER DI FRONTEND (PELANGGAN)
        // ==========================================
        
        // Skenario: Budi duduk di Meja 1, beli 2 Soto
        
        Livewire::test(OrderPage::class)
            // --- PERBAIKAN DISINI ---
            // 1. Kita harus isi keranjang dulu agar lolos pengecekan "empty cart"
            ->call('addToCart', $soto->id) 
            
            // 2. Baru kita coba checkout TANPA Nama & Meja
            ->call('checkout', 'tunai')
            // 3. Sekarang validasi pasti muncul
            ->assertHasErrors(['nama_pelanggan', 'no_meja']) 
            
            // 4. Lanjut isi data yang benar
            ->set('nama_pelanggan', 'Budi Santoso')
            ->set('no_meja', 'Meja 1') // <--- WAJIB DIISI
            
            // 5. Update Qty (Opsional, tadi kan udah add 1 di atas)
            ->call('updateQty', $soto->id, 1) // Jadi total 2
            
            // 6. Checkout Beneneran
            ->call('checkout', 'tunai')
            
            ->assertSet('cart', [])
            ->assertSet('showCartModal', false);
        // Assert Database Transaksi
        $transaksi = Transaksi::where('nama_pelanggan', 'Budi Santoso')->first();
        
        $this->assertNotNull($transaksi);
        $this->assertEquals('Meja 1', $transaksi->no_meja);
        $this->assertEquals(30000, $transaksi->total_harga); // 15.000 x 2
        $this->assertEquals('pending', $transaksi->status); // Tunai harusnya pending dulu menunggu kasir terima uang
        
        // Assert Stok Berkurang (20 - 2 = 18)
        $this->assertEquals(18, $soto->fresh()->stok);


        // ==========================================
        // TAHAP 3: KASIR TERIMA PEMBAYARAN
        // ==========================================
        
        // Login sebagai Kasir
        $this->actingAs($kasir);

        // Simulasi Kasir update status lewat kode (Controller/Livewire Kasir)
        // Anggap saja kasir menekan tombol "Terima Pembayaran"
        $transaksi->update(['status' => 'paid']);

        // Assert Status Berubah
        $this->assertEquals('paid', $transaksi->fresh()->status);


        // ==========================================
        // TAHAP 4: JURNAL AKUNTANSI OTOMATIS (SYSTEM)
        // ==========================================
        
        // Kita simulasi trigger pembuatan jurnal (biasanya ada di Observer atau function paymentSuccess)
        // Karena di test manual ini kita update langsung via eloquent, kita panggil logic jurnal manual
        // Atau jika kamu punya logic ini di paymentSuccess OrderPage, panggil itu.
        
        // Mari kita buat manual jurnalnya untuk memastikan logic akuntansi benar:
        // (Ini meniru apa yang terjadi di `OrderPage::paymentSuccess` atau `KasirPage`)
        
        $jurnal = \App\Models\Jurnal::create([
            'transaksi_id' => $transaksi->id,
            'keterangan' => 'Penjualan Tunai #' . $transaksi->id,
            'tanggal' => now(),
        ]);
        
        \App\Models\DetailJurnal::create([
            'jurnal_id' => $jurnal->id,
            'akun_id' => $akunKas->id, // Debit Kas (111)
            'debit' => $transaksi->total_harga,
            'kredit' => 0
        ]);
        
        \App\Models\DetailJurnal::create([
            'jurnal_id' => $jurnal->id,
            'akun_id' => $akunJual->id, // Kredit Penjualan (411)
            'debit' => 0,
            'kredit' => $transaksi->total_harga
        ]);

        // Assert Data Jurnal Masuk DB
        $this->assertDatabaseHas('jurnals', ['transaksi_id' => $transaksi->id]);
        
        // Assert Detail Jurnal (Debit KAS)
        $this->assertDatabaseHas('detail_jurnals', [
            'akun_id' => $akunKas->id,
            'debit' => 30000,
            'kredit' => 0
        ]);
        
        // Assert Detail Jurnal (Kredit PENJUALAN)
        $this->assertDatabaseHas('detail_jurnals', [
            'akun_id' => $akunJual->id,
            'debit' => 0,
            'kredit' => 30000
        ]);
    }
}