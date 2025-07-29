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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('nama_pemain');
            $table->integer('tinggi_badan'); // dalam cm
            $table->integer('berat_badan'); // dalam kg
            $table->enum('posisi_pemain', ['Penyerang', 'Gelandang', 'Bertahan', 'Penjaga Gawang']);
            $table->integer('nomor_punggung');
            $table->timestamps();

            // Constraint untuk memastikan nomor punggung unik dalam satu tim
            $table->unique(['team_id', 'nomor_punggung']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
