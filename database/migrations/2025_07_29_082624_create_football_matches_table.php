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
        Schema::create('football_matches', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pertandingan');
            $table->time('waktu_pertandingan');
            $table->foreignId('tim_tuan_rumah')->constrained('teams')->onDelete('cascade');
            $table->foreignId('tim_tamu')->constrained('teams')->onDelete('cascade');
            $table->string('tempat_pertandingan')->nullable();
            $table->enum('status_pertandingan', ['Terjadwal', 'Berlangsung', 'Selesai', 'Ditunda', 'Dibatalkan'])->default('Terjadwal');
            $table->timestamps();

            // Pastikan tim tuan rumah tidak sama dengan tim tamu
            $table->index(['tanggal_pertandingan', 'waktu_pertandingan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('football_matches');
    }
};
