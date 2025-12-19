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
        // Schema::table('build_details', function (Blueprint $table) {
        //     $table->dropForeign(['id_produk']);
        //     $table->dropColumn('id_produk');
        //     $table->foreignId('id_varian')
        //         ->constrained('variants', 'id_varian')
        //         ->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('build_details', function (Blueprint $table) {
        //     $table->dropForeign(['id_varian']);
        //     $table->dropColumn('id_varian');
        //     $table->foreignId('id_produk')
        //         ->constrained('products', 'id_produk')
        //         ->onDelete('cascade');
        // });
    }
};
