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
        Schema::create('match_games', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pertandingan');
            $table->time('waktu_pertandingan');
            $table->foreignId('tim_tuan_rumah_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('tim_tamu_id')->constrained('teams')->onDelete('cascade');
            $table->string('tempat_pertandingan')->nullable();
            $table->enum('status_pertandingan', ['Dijadwalkan', 'Berlangsung', 'Selesai', 'Dibatalkan'])->default('Dijadwalkan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['tanggal_pertandingan', 'waktu_pertandingan']);
            $table->index('tim_tuan_rumah_id');
            $table->index('tim_tamu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_games');
    }
};
