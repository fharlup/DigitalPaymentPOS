<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DetailJurnal extends Model
{
    use HasFactory;

    protected $fillable = ['jurnal_id', 'akun_id', 'debit', 'kredit'];

    public function akun()
    {
        return $this->belongsTo(Akun::class);
    }
    
    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }
}
