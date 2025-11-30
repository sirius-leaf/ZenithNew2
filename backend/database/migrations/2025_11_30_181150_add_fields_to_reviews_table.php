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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('id_variant')->nullable()->constrained('variants', 'id_varian')->onDelete('set null');
            $table->foreignId('id_pesanan')->nullable()->constrained('pesanans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['id_variant']);
            $table->dropForeign(['id_pesanan']);
            $table->dropColumn(['id_variant', 'id_pesanan']);
        });
    }
};
