<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Kasir yang input
    $table->string('nama_pelanggan')->nullable(); // Opsional
    $table->decimal('total_harga', 10, 2)->default(0);
    $table->enum('metode_pembayaran', ['tunai', 'qris'])->nullable();
    $table->enum('status', ['pending', 'paid'])->default('pending'); // Status bayar
    $table->date('tanggal_transaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
