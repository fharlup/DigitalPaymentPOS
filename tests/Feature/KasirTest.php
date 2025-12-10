<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\Akun;
use App\Livewire\KasirPage;
use Livewire\Livewire;

class KasirTest extends TestCase
{
    use RefreshDatabase;

    // Fungsi bantu untuk bikin akun COA wajib (biar gak error logic)
    private function seedAkunWajib()
    {
        Akun::create(['kode_akun' => '111', 'nama_akun' => 'Kas', 'tipe' => 'debit']);
        Akun::create(['kode_akun' => '112', 'nama_akun' => 'Bank', 'tipe' => 'debit']);
        Akun::create(['kode_akun' => '411', 'nama_akun' => 'Penjualan', 'tipe' => 'kredit']);
    }

    public function test_halaman_kasir_tidak_bisa_diakses_tanpa_login()
    {
        $response = $this->get('/kasir');
        $response->assertRedirect('/login'); // Harus ditendang ke login
    }

    public function test_kasir_bisa_melihat_transaksi_pending()
    {
        $this->seedAkunWajib();
        
        // 1. Login sebagai Kasir
        $kasir = User::factory()->create(['role' => 'kasir']);
        
        // 2. Buat Transaksi Pending (Pura-puranya ada pesanan masuk)
        Transaksi::create([
            'user_id' => $kasir->id,
            'nama_pelanggan' => 'Antrian 1',
            'total_harga' => 20000,
            'status' => 'pending',
            'tanggal_transaksi' => now(),
        ]);

        // 3. Cek Halaman Kasir
        $this->actingAs($kasir)
             ->get('/kasir')
             ->assertSee('Antrian 1') // Harus muncul di layar
             ->assertSee('Rp 20.000');
    }

    public function test_pembayaran_tunai_membuat_jurnal_otomatis()
    {
        $this->seedAkunWajib();
        $kasir = User::factory()->create(['role' => 'kasir']);

        // 1. Ada pesanan pending
        $transaksi = Transaksi::create([
            'user_id' => $kasir->id,
            'nama_pelanggan' => 'Pak Bos',
            'total_harga' => 50000,
            'status' => 'pending',
            'tanggal_transaksi' => now(),
        ]);

        // 2. Kasir Klik "Bayar Tunai" via Livewire
        Livewire::actingAs($kasir)
            ->test(KasirPage::class)
            ->call('bayarTunai', $transaksi->id);

        // 3. CEK HASIL (PENTING BUAT TA!)
        
        // A. Status harus berubah jadi PAID
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => 'paid',
            'metode_pembayaran' => 'tunai',
        ]);

        // B. Jurnal Header harus terbentuk
        $this->assertDatabaseHas('jurnals', [
            'transaksi_id' => $transaksi->id,
            'keterangan' => 'Penjualan (TUNAI) #' . $transaksi->id,
        ]);

        // C. Detail Jurnal (Debit KAS 50.000)
        $this->assertDatabaseHas('detail_jurnals', [
            'debit' => 50000,
            'kredit' => 0,
        ]);
        
        // D. Detail Jurnal (Kredit PENJUALAN 50.000)
        $this->assertDatabaseHas('detail_jurnals', [
            'debit' => 0,
            'kredit' => 50000,
        ]);
    }
}