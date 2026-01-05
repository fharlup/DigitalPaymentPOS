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
        Schema::table('transaksis', function (Blueprint $table) {
            //
            DB::statement("ALTER TABLE transaksis MODIFY COLUMN status ENUM('pending', 'paid', 'done') NOT NULL DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            DB::statement("ALTER TABLE transaksis MODIFY COLUMN status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending'");
        });
    }
};
