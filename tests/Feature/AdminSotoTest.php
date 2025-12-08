<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Akun;

class AdminSotoTest extends TestCase
{
    // Ini agar database di-reset bersih setiap kali tes jalan
    use RefreshDatabase;

    // 1. Cek Halaman Login
    public function test_halaman_login_bisa_dibuka()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    // 2. Cek Login Admin
    public function test_admin_bisa_login_dan_masuk_dashboard()
    {
        $admin = User::factory()->create([
            'email' => 'admin@soto.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200);
    }

    // 3. Cek Keamanan (Kasir dilarang masuk admin panel)
    public function test_kasir_tidak_bisa_masuk_halaman_admin_soto()
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
        ]);

        $this->actingAs($kasir)
            ->get('/admin')
            ->assertStatus(403); // Forbidden
    }

    // 4. Cek Master Data Kategori
    public function test_bisa_tambah_kategori_menu_baru()
    {
        Kategori::create([
            'nama_kategori' => 'Makanan Berat',
        ]);

        $this->assertDatabaseHas('kategoris', [
            'nama_kategori' => 'Makanan Berat',
        ]);
    }

    // 5. Cek Master Data Produk & Stok
    public function test_bisa_tambah_produk_soto_dan_stok_tersimpan()
    {
        $kategori = Kategori::create(['nama_kategori' => 'Makanan']);

        Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'Soto Spesial',
            'harga' => 15000,
            'stok' => 100,
        ]);

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Soto Spesial',
            'harga' => 15000,
            'stok' => 100,
        ]);
    }

    // 6. Cek Akuntansi (COA)
    public function test_bisa_buat_akun_coa()
    {
        Akun::create([
            'kode_akun' => '111',
            'nama_akun' => 'Kas Tunai',
            'tipe' => 'debit',
        ]);

        $this->assertDatabaseHas('akuns', [
            'kode_akun' => '111',
            'nama_akun' => 'Kas Tunai',
        ]);
    }
}