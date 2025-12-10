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

public function test_checkout_tunai_berhasil_membuat_transaksi_pending()
    {
        // 0. BUAT USER ID 1 (WAJIB ADA)
        // Karena di OrderPage.php kita hardcode 'user_id' => 1
        \App\Models\User::factory()->create([
            'id' => 1,
            'role' => 'admin' // role bebas, yang penting ID-nya 1
        ]);

        // 1. Siapkan Data Produk
        $kategori = Kategori::create(['nama_kategori' => 'Minuman']);
        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Es Teh',
            'harga' => 3000,
            'stok' => 50,
        ]);

        // 2. Simulasi Proses Pesan sampai Checkout
        Livewire::test(OrderPage::class)
            ->set('nama_pelanggan', 'Siti')
            ->call('addToCart', $produk->id)
            ->call('checkout', 'tunai'); // Klik Bayar Tunai

        // 3. Cek Database: Harusnya ada transaksi PENDING (Belum Lunas)
        $this->assertDatabaseHas('transaksis', [
            'nama_pelanggan' => 'Siti',
            'total_harga' => 3000,
            'status' => 'pending', 
            'metode_pembayaran' => 'tunai',
            'user_id' => 1 // Memastikan tersimpan dengan ID user yang benar
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
            ->assertSee('Maaf, stok habis!'); // Cek pesan error session
    }
}