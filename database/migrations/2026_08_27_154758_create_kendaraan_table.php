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
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_polisi')->unique();
            $table->string('merk');
            $table->string('tipe');
            $table->integer('tahun');
            $table->string('warna');
            $table->string('jenis_kendaraan');
            $table->integer('kapasitas');
            $table->string('nomor_mesin')->nullable()->unique();
            $table->string('nomor_rangka')->nullable()->unique();
            $table->date('tanggal_pajak')->nullable();
            $table->date('tanggal_stnk')->nullable();
            $table->string('status')->default('aktif');
            $table->text('keterangan')->nullable();
            $table->integer('banyak_level_persetujuan')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
