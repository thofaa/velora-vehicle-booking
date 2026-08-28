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
        Schema::create('konsumsi_bbm', function (Blueprint $table) {
            $table->bigIncrements('id_konsumsi');
            $table->foreignId('id_kendaraan')->constrained('kendaraan');
            $table->date('tanggal');
            $table->decimal('jumlah_liter', 10, 2);
            $table->foreignId('id_pemesanan')->nullable()->constrained('pemesanan');
            $table->timestamps();

            $table->index(['id_kendaraan', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsumsi_bbm');
    }
};
