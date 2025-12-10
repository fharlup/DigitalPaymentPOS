<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Akun;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Livewire\OrderPage;
use App\Livewire\KasirPage;
use Livewire\Livewire;

class IntegrasiFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_lengkap_dari_admin_sampai_kasir()
    {
        
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'role' => 'admin'
        ]);

        $kasir = User::factory()->create([
            'name' => 'Mbak Kasir',
            'email' => 'kasir@soto.com',
            'role' => 'kasir'
        ]);

        $this->assertDatabaseHas('users', ['email' => 'kasir@soto.com']);


        // ==========================================
        // TAHAP 2: ADMIN BUAT MASTER DATA (COA, KATEGORI, PRODUK)
        // ==========================================
        
        // 1. Admin buat COA (Wajib ada biar gak error jurnal)
        Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas Tunai', 'tipe' => 'debit']);
        Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank', 'tipe' => 'debit']);
        Akun::create(['kode_akun' => '411', 'nama_akun' => 'Penjualan', 'tipe' => 'kredit']);

        // 2. Admin buat Kategori & Produk
        $kategori = Kategori::create(['nama_kategori' => 'Makanan']);
        
        // Stok awal Soto = 10 Mangkok
        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Ayam Spesial',
            'harga' => 15000,
            'stok' => 10, 
            'gambar' => 'soto.jpg'
        ]);

        // Assert: Pastikan produk ada di DB dengan stok 10
        $this->assertDatabaseHas('produks', ['nama_produk' => 'Soto Ayam Spesial', 'stok' => 10]);


        // ==========================================
        // TAHAP 3: PELANGGAN MEMBELI (ORDER)
        // ==========================================
        // Ceritanya: Pelanggan Budi beli 2 Mangkok Soto

        Livewire::test(OrderPage::class)
            ->set('nama_pelanggan', 'Budi')
            ->call('addToCart', $produk->id) // Klik tambah
            ->call('updateQty', $produk->id, 1) // Tambah 1 lagi jadi 2 (Simulasi tombol +)
            ->call('checkout', 'tunai'); // Bayar di kasir

        // Cek Transaksi Masuk
        $transaksi = Transaksi::where('nama_pelanggan', 'Budi')->first();
        
        // Assert: Status harus PENDING
        $this->assertEquals('pending', $transaksi->status);
        $this->assertEquals(30000, $transaksi->total_harga); // 15.000 x 2

        // Assert: CEK STOK BERKURANG?
        // Stok awal 10, beli 2, harusnya sisa 8
        $this->assertEquals(8, $produk->fresh()->stok);


        // ==========================================
        // TAHAP 4: KASIR MEMPROSES PEMBAYARAN
        // ==========================================
        
        // Login sebagai Kasir
        $this->actingAs($kasir);

        // Simulasi Kasir membuka halaman dan menekan tombol "Bayar Tunai"
        Livewire::test(KasirPage::class)
            ->assertSee('Budi') // Kasir harus lihat pesanan Budi di layar
            ->call('bayarTunai', $transaksi->id);

        // Assert: Status Transaksi berubah jadi PAID
        $this->assertEquals('paid', $transaksi->fresh()->status);

        // Assert: Cek Jurnal Akuntansi Terbentuk
        $this->assertDatabaseHas('jurnals', [
            'transaksi_id' => $transaksi->id,
            'keterangan' => 'Penjualan (TUNAI) #' . $transaksi->id
        ]);

        // Assert: Cek Uang Masuk ke Kas (Debit 111 senilai 30.000)
        $this->assertDatabaseHas('detail_jurnals', [
            'debit' => 30000,
            'kredit' => 0
        ]);
    }
}