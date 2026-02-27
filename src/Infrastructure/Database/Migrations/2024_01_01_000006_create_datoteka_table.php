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
        Schema::create('datoteka', function (Blueprint $table) {
            $table->id('idDatoteka');
            $table->string('putanja');
            $table->string('tip');
            $table->unsignedBigInteger('idPoruka');
            $table->timestamps();

            $table->foreign('idPoruka')->references('idPoruka')->on('poruka')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datoteka');
    }
};

