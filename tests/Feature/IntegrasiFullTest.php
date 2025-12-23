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
        
        // 1. Buat User
        $admin = User::factory()->create(['name' => 'Juragan', 'role' => 'admin']);
        $kasir = User::factory()->create(['name' => 'Mbak Kasir', 'role' => 'kasir']);

        // 2. Buat Data Meja
        $meja1 = Meja::create(['nomor_meja' => 'Meja 1']);

        // 3. Buat COA (Chart of Accounts)
        $akunKas = Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit']);
        $akunBank = Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank BCA', 'tipe' => 'debit']);
        $akunJual = Akun::create(['kode_akun' => '411', 'nama_akun' => 'Penjualan', 'tipe' => 'kredit']);

        // 4. Buat Kategori & Produk
        $kategori = Kategori::create(['nama_kategori' => 'Makanan Berat']);
        
        $soto = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Ayam Kampung',
            'harga' => 15000,
            'stok' => 20,
        ]);

        // ==========================================
        // TAHAP 2: USER ORDER DI FRONTEND (PELANGGAN)
        // ==========================================
        
        Livewire::test(OrderPage::class)
            // A. Masukkan barang ke keranjang dulu (agar lolos cek empty cart)
            ->call('addToCart', $soto->id)
            
            // B. Coba Checkout TANPA Nama & Meja (Harus Gagal)
            ->call('checkout', 'tunai')
            ->assertHasErrors(['nama_pelanggan', 'no_meja']) 
            
            // C. Lengkapi Data
            ->set('nama_pelanggan', 'Budi Santoso')
            ->set('no_meja', 'Meja 1')
            
            // D. Tambah Qty jadi 2 (Opsional, untuk tes updateQty)
            ->call('updateQty', $soto->id, 1) // Tambah 1 lagi
            
            // E. Checkout Berhasil
            ->call('checkout', 'tunai')
            ->assertSet('cart', []) // Keranjang harus kosong setelah sukses
            ->assertSet('showCartModal', false); // Modal tertutup

        // Assert Database Transaksi Terbuat
        $transaksi = Transaksi::where('nama_pelanggan', 'Budi Santoso')->first();
        
        $this->assertNotNull($transaksi);
        $this->assertEquals('Meja 1', $transaksi->no_meja);
        $this->assertEquals(30000, $transaksi->total_harga); // 15.000 x 2
        $this->assertEquals('pending', $transaksi->status); // Status awal pending
        
        // Assert Stok Berkurang (20 - 2 = 18)
        $this->assertEquals(18, $soto->fresh()->stok);


        // ==========================================
        // TAHAP 3: KASIR TERIMA PEMBAYARAN
        // ==========================================
        
        $this->actingAs($kasir);

        // Simulasi Kasir menekan tombol "Terima Pembayaran" / "Paid"
        // Idealnya ini memanggil endpoint/livewire method, tapi update model langsung 
        // sah-sah saja untuk memicu Observer/Event.
        $transaksi->update(['status' => 'paid']);

        // Assert Status Berubah
        $this->assertEquals('paid', $transaksi->fresh()->status);


        // ==========================================
        // TAHAP 4: ASSERT JURNAL OTOMATIS (SYSTEM)
        // ==========================================
        
        /* CATATAN: 
           Di tahap ini, Test TIDAK BOLEH membuat jurnal manual.
           Test hanya bertugas mengecek apakah SYSTEM kamu sudah otomatis membuat jurnal 
           ketika status berubah jadi 'paid'.
        */

        // 1. Cek apakah Header Jurnal terbentuk
        $this->assertDatabaseHas('jurnals', [
            'transaksi_id' => $transaksi->id,
            // 'keterangan' => 'Penjualan Tunai', // Opsional, sesuaikan dengan logic kodemu
        ]);

        $jurnal = \App\Models\Jurnal::where('transaksi_id', $transaksi->id)->first();
        $this->assertNotNull($jurnal, 'Jurnal gagal dibuat otomatis oleh sistem!');

        // 2. Cek Detail Jurnal (DEBIT: KAS)
        $this->assertDatabaseHas('detail_jurnals', [
            'jurnal_id' => $jurnal->id,
            'akun_id' => $akunKas->id, // Pastikan logic kodemu mengambil akun kas (111)
            'debit' => 30000,
            'kredit' => 0
        ]);
        
        // 3. Cek Detail Jurnal (KREDIT: PENJUALAN)
        $this->assertDatabaseHas('detail_jurnals', [
            'jurnal_id' => $jurnal->id,
            'akun_id' => $akunJual->id, // Pastikan logic kodemu mengambil akun penjualan (411)
            'debit' => 0,
            'kredit' => 30000
        ]);
    }
}