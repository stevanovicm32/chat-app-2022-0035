<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sankcija', function (Blueprint $table) {
            $table->id('idSankcija');
            $table->date('datum_isteka');
            $table->unsignedBigInteger('moderator_id_ref');
            $table->unsignedBigInteger('idPrijava');
            $table->timestamps();

            $table->foreign('idPrijava')->references('idPrijava')->on('prijava')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sankcija');
    }
};
