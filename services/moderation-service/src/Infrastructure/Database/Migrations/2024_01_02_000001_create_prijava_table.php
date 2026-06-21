<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prijava', function (Blueprint $table) {
            $table->id('idPrijava');
            $table->unsignedBigInteger('podnosilac_id_ref');
            $table->unsignedBigInteger('poruka_id_ref')->nullable();
            $table->unsignedBigInteger('optuzeni_id_ref')->nullable();
            $table->enum('status', ['na_cekanju', 'odobreno', 'odbijeno'])->default('na_cekanju');
            $table->date('datum');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prijava');
    }
};
