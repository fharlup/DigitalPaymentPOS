<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Produk extends Model
{
  use HasFactory;

    protected $fillable = [
        'kategori_id',
        'nama_produk',
        'deskripsi',
        'gambar',
        'harga',
        'stok',
    ];

    // Relasi: Produk milik satu kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    } 
}
