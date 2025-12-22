<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('transaksis', function (Blueprint $table) {
        // Tambah kolom no_meja setelah nama_pelanggan
        $table->string('no_meja')->nullable()->after('nama_pelanggan');
    });
}

public function down(): void
{
    Schema::table('transaksis', function (Blueprint $table) {
        $table->dropColumn('no_meja');
    });
}
   

};
