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
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_game_id')->constrained('match_games')->onDelete('cascade');
            $table->integer('skor_tim_tuan_rumah')->default(0);
            $table->integer('skor_tim_tamu')->default(0);
            $table->text('catatan_hasil')->nullable();
            $table->timestamp('waktu_laporan')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index('match_game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
