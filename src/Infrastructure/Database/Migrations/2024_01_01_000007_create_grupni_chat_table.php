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
        Schema::create('grupni_chat', function (Blueprint $table) {
            $table->unsignedBigInteger('idChat')->primary();
            $table->string('naziv');
            $table->timestamps();

            $table->foreign('idChat')->references('idChat')->on('chat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupni_chat');
    }
};

