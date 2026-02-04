<?php

use Illuminate\Support\Facades\Route;
// PENTING: Import component Livewire di sini agar dikenali
use App\Livewire\OrderPage;
use App\Livewire\KasirPage;
use App\Models\Transaksi;
use App\Livewire\LoginPage;
Route::get('/', function () { return redirect()->route('pesan'); });
Route::get('/pesan', OrderPage::class)->name('pesan');

// 2. Route Login (Hanya untuk yang BELUM login)
Route::get('/login', LoginPage::class)->name('login')->middleware('guest');

// 3. Route Terproteksi (Hanya Kasir/Admin yang SUDAH login)
Route::get('/kasir', KasirPage::class)->name('kasir')->middleware('auth');

// 4. Logout (Opsional via route)
Route::get('/logout', function () {
    auth()->logout();
    return redirect()->route('login');
})->name('logout');
Route::get('/struk/{id}', function ($id) {
    $transaksi = Transaksi::findOrFail($id);

    // LOGIC PENGAMAN:
    // Jika status masih 'pending' atau 'failed', tendang balik ke menu utama
    if ($transaksi->status == 'pending' || $transaksi->status == 'failed') {
        return redirect('/')->with('error', 'Eits! Pesanan belum dibayar, tidak bisa lihat struk.');
    }

    // Kalau sudah 'paid' atau 'done', baru boleh lihat
    return view('struk_digital', compact('transaksi'));
})->name('struk.digital');