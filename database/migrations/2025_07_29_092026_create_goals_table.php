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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_result_id')->constrained('match_results')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('menit_gol'); // Waktu terjadinya gol dalam menit
            $table->integer('detik_gol')->default(0); // Waktu dalam detik (opsional)
            $table->enum('jenis_gol', ['Normal', 'Penalti', 'Own Goal', 'Free Kick'])->default('Normal');
            $table->text('deskripsi_gol')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index('match_result_id');
            $table->index('player_id');
            $table->index('team_id');
            $table->index(['menit_gol', 'detik_gol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
