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
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();

            // Relasi ke siapa yang memesan
            $table->foreignId('user_id')->constrained('users');

            // Relasi ke toko siapa yang dijual (PENTING)
            $table->foreignId('toko_id')->constrained('tokos');

            $table->decimal('total_harga', 15, 2);
            $table->string('status')->default('pending'); // pending, paid, processing, shipped, completed
            $table->text('alamat_pengiriman');
            // Anda bisa tambah info lain (metode pembayaran, resi, dll)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
