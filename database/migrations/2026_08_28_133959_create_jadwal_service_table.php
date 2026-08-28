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
        Schema::create('jadwal_service', function (Blueprint $table) {
            $table->bigIncrements('id_jadwal_service');
            $table->foreignId('id_kendaraan')->constrained('kendaraan');
            $table->date('tanggal_service');
            $table->string('jenis_service');
            $table->string('status');
            $table->timestamps();

            $table->index(['id_kendaraan', 'tanggal_service']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_service');
    }
};
