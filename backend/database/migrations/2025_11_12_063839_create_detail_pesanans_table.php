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
        Schema::create('detail_pesanans', function (Blueprint $table) {
            $table->id();

            // Relasi ke pesanan mana
            $table->foreignId('pesanan_id')->constrained('pesanans')->onDelete('cascade');

            // Relasi ke varian produk mana yang dibeli (BUKAN product_id)
            // Variants table uses a non-standard primary key name 'id_varian', so we must reference that explicitly
            $table->foreignId('id_varian')->constrained('variants', 'id_varian')->onDelete('cascade');

            $table->integer('kuantitas');
            $table->decimal('harga', 15, 2); // Harga satuan (snapshot saat beli)

            // Tidak perlu timestamps di sini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanans');
    }
};
