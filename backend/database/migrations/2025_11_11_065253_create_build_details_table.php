<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('build_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_build')
                ->constrained('pc_builds', 'id_build')
                ->onDelete('cascade');
            $table->foreignId('id_produk')
                ->constrained('products', 'id_produk')
                ->onDelete('cascade');
            $table->enum('bagian_komponen', ['motherboard', 'cpu', 'ram', 'psu', 'storage']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('build_details');
    }
};
