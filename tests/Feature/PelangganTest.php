<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Livewire\OrderPage;
use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Livewire;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_menu_bisa_dibuka()
    {
        $response = $this->get('/pesan');
        $response->assertStatus(200);
    }

    public function test_pelanggan_bisa_memasukkan_keranjang()
    {
        // 1. Siapkan Data Produk
        $kategori = Kategori::create(['nama_kategori' => 'Makanan']);
        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Ayam',
            'harga' => 15000,
            'stok' => 50,
        ]);

        // 2. Simulasi Livewire (Pilih Barang)
        Livewire::test(OrderPage::class)
            ->set('nama_pelanggan', 'Budi') // Isi nama
            ->call('addToCart', $produk->id) // Klik tambah
            ->assertSee('Soto Ayam') // Pastikan muncul di state
            ->assertSet('cart.' . $produk->id . '.jumlah', 1); // Cek jumlah 1
    }

public function checkout_tunai_berhasil_membuat_transaksi_pending()
    {
        // 1. Setup Data
        $kategori = \App\Models\Kategori::create(['nama_kategori' => 'Makanan']);
        $produk = \App\Models\Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Es Teh',
            'harga' => 3000,
            'stok' => 10,
            'gambar' => null
        ]);
        
        // Setup Meja (PENTING: Karena sekarang butuh data meja)
        \App\Models\Meja::create(['nomor_meja' => 'Meja 1']);

        // 2. Action (Livewire)
        \Livewire\Livewire::test(\App\Livewire\OrderPage::class)
            ->set('nama_pelanggan', 'Siti') // Set Nama
            ->set('no_meja', 'Meja 1')      // <--- TAMBAHKAN INI (WAJIB)
            ->call('addToCart', $produk->id)
            ->call('checkout', 'tunai');    // Klik Bayar

        // 3. Cek Database
        $this->assertDatabaseHas('transaksis', [
            'nama_pelanggan' => 'Siti',
            'total_harga' => 3000,
            'status' => 'pending',
            'metode_pembayaran' => 'tunai',
            // 'no_meja' => 'Meja 1' // Opsional: Cek mejanya juga masuk
        ]);
    }
    public function test_stok_habis_tidak_bisa_dipesan()
    {
        $kategori = Kategori::create(['nama_kategori' => 'Makanan']);
        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Habis',
            'harga' => 15000,
            'stok' => 0, // Stok Kosong
        ]);

        Livewire::test(OrderPage::class)
            ->call('addToCart', $produk->id)
            ->assertSee('Stok habis!'); // Cek pesan error session
    }
}