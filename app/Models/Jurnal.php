<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Jurnal extends Model
{
  use HasFactory;

    protected $fillable = ['tanggal', 'keterangan', 'transaksi_id'];

    // Relasi: Satu jurnal punya banyak baris detail
    public function detailJurnals()
    {
        return $this->hasMany(DetailJurnal::class);
    } 
}
