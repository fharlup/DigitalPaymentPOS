<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Transaksi extends Model
{
    use HasFactory;

    // IZINKAN KOLOM INI DIISI OTOMATIS
    protected $fillable = [
        'user_id',
        'nama_pelanggan',
        'no_meja',
        'total_harga',
        'metode_pembayaran',
        'status',
        'tanggal_transaksi',
        'snap_token', // Penting buat Midtrans nanti
    ];

    // Relasi ke User (Kasir)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Detail Transaksi (Item Belanjaan)
    public function details()
    {
        return $this->hasMany(DetailTransaksi::class);
    }
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }
}