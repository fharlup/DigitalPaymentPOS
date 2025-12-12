<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'subtotal',
    ];

    // --- TAMBAHKAN BAGIAN INI ---
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
    // ----------------------------

    // Relasi ke Produk (Opsional, buat jaga-jaga kalau butuh nanti)
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}