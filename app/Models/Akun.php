<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Akun extends Model
{
   use HasFactory;

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe', // 'debit' atau 'kredit' (saldo normal)
    ];
    public function detailJurnals()
    {
        return $this->hasMany(DetailJurnal::class);
    }
}
