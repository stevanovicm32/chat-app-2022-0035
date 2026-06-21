<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Izmena postojeće kolone i dodavanje dodatnih ograničenja:
     * - Dodavanje indeksa na email kolonu za brže pretrage
     * - Izmena suspendovan kolone - eksplicitno postavljanje nullable
     * - Dodavanje indeksa na idUloga za brže join operacije
     */
    public function up(): void
    {
        Schema::table('korisnik', function (Blueprint $table) {
            // Dodavanje indeksa na email kolonu za brže pretrage
            // (email je već unique, ali dodatni index može pomoći u nekim slučajevima)
            $table->index('email', 'idx_korisnik_email');
            
            // Dodavanje indeksa na idUloga za brže join operacije sa uloga tabelom
            $table->index('idUloga', 'idx_korisnik_uloga');
            
            // Izmena suspendovan kolone - eksplicitno postavljanje nullable
            // (već je nullable, ali ovo je primer kako se menja postojeća kolona)
            $table->date('suspendovan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('korisnik', function (Blueprint $table) {
            // Uklanjanje indeksa
            $table->dropIndex('idx_korisnik_email');
            $table->dropIndex('idx_korisnik_uloga');
            
            // Vraćanje suspendovan kolone na prethodno stanje
            $table->date('suspendovan')->nullable()->change();
        });
    }
};

