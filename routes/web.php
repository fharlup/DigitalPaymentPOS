<?php

use Illuminate\Support\Facades\Route;
// PENTING: Import component Livewire di sini agar dikenali
use App\Livewire\OrderPage;
use App\Livewire\KasirPage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 1. Route untuk Halaman Pelanggan (Menu Pemesanan)
// Saat akses: http://127.0.0.1:8000/pesan
Route::get('/pesan', OrderPage::class)->name('pesan');

// 2. Route untuk Halaman Kasir (Proses Bayar)
// Saat akses: http://127.0.0.1:8000/kasir
Route::get('/kasir', KasirPage::class)->name('kasir');

// 3. Redirect Halaman Utama
// Kalau buka http://127.0.0.1:8000/ (kosong), otomatis lempar ke /pesan
Route::get('/', function () {
    return redirect()->route('pesan');
});

// Catatan: Route untuk Admin Panel (/admin) sudah otomatis diurus oleh Filament
// Jadi tidak perlu ditulis manual di sini.