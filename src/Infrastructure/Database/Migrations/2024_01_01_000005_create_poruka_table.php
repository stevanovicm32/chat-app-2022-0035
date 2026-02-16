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
        Schema::create('poruka', function (Blueprint $table) {
            $table->id('idPoruka');
            $table->text('tekst');
            $table->unsignedBigInteger('idChat');
            $table->timestamps();

            $table->foreign('idChat')->references('idChat')->on('chat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poruka');
    }
};

