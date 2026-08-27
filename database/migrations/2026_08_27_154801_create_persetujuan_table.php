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
        Schema::create('persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pemesanan')->constrained('pemesanan');
            $table->integer('level_persetujuan');
            $table->foreignId('id_pihak_penyetuju')->constrained('users');
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['id_pemesanan', 'level_persetujuan']);
            $table->unique(['id_pemesanan', 'id_pihak_penyetuju']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persetujuan');
    }
};
