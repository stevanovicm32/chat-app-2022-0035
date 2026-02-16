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
        Schema::create('pripada', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idKorisnik');
            $table->unsignedBigInteger('idChat');
            $table->date('datumKreiranja');
            $table->timestamps();

            $table->foreign('idKorisnik')->references('idKorisnik')->on('korisnik')->onDelete('cascade');
            $table->foreign('idChat')->references('idChat')->on('chat')->onDelete('cascade');
            $table->unique(['idKorisnik', 'idChat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pripada');
    }
};

