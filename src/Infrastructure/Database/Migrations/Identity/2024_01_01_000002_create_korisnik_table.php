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
        Schema::create('korisnik', function (Blueprint $table) {
            $table->id('idKorisnik');
            $table->string('email')->unique();
            $table->string('lozinka');
            $table->date('suspendovan')->nullable();
            $table->unsignedBigInteger('idUloga');
            $table->timestamps();

            $table->foreign('idUloga')->references('idUloga')->on('uloga')->onDelete('restrict');
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('korisnik');
    }
};

