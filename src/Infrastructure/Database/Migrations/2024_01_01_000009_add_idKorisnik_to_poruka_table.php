<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Dodavanje kolone idKorisnik u poruka tabelu
     * da bismo znali ko je poslao poruku
     */
    public function up(): void
    {
        Schema::table('poruka', function (Blueprint $table) {
            $table->unsignedBigInteger('idKorisnik')->nullable()->after('idChat');
            $table->foreign('idKorisnik')
                  ->references('idKorisnik')
                  ->on('korisnik')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poruka', function (Blueprint $table) {
            $table->dropForeign(['idKorisnik']);
            $table->dropColumn('idKorisnik');
        });
    }
};

