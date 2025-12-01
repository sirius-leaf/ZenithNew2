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
        Schema::table('build_details', function (Blueprint $table) {
            $table->enum('bagian_komponen', ['motherboard', 'cpu', 'ram', 'psu', 'storage', 'cooler', 'video-card', 'case', 'monitor', 'mouse', 'keyboard'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('build_details', function (Blueprint $table) {
            $table->enum('bagian_komponen', ['motherboard', 'cpu', 'ram', 'psu', 'storage'])->change();
        });
    }
};