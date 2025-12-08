<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kategori extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori'];

    // Relasi: Satu kategori punya banyak produk
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}
