<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Dodavanje dodatnih indeksa i ograničenja:
     * - Indeks na datumKreiranja u pripada tabeli
     * - Indeks na idChat u poruka tabeli
     * - Composite index za brže pretrage
     */
    public function up(): void
    {
        // Dodavanje indeksa na pripada tabelu
        Schema::table('pripada', function (Blueprint $table) {
            // Indeks na datumKreiranja za brže sortiranje i filtriranje
            $table->index('datumKreiranja', 'idx_pripada_datum_kreiranja');
            
            // Composite index za brže pretrage po korisniku i datumu
            $table->index(['idKorisnik', 'datumKreiranja'], 'idx_pripada_korisnik_datum');
        });

        // Dodavanje indeksa na poruka tabelu
        Schema::table('poruka', function (Blueprint $table) {
            // Indeks na idChat za brže dohvatanje poruka iz chata
            $table->index('idChat', 'idx_poruka_chat');
            
            // Indeks na created_at za sortiranje poruka po vremenu
            $table->index('created_at', 'idx_poruka_created_at');
        });

        // Dodavanje indeksa na datoteka tabelu
        Schema::table('datoteka', function (Blueprint $table) {
            // Indeks na tip fajla za filtriranje po tipu
            $table->index('tip', 'idx_datoteka_tip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pripada', function (Blueprint $table) {
            $table->dropIndex('idx_pripada_datum_kreiranja');
            $table->dropIndex('idx_pripada_korisnik_datum');
        });

        Schema::table('poruka', function (Blueprint $table) {
            $table->dropIndex('idx_poruka_chat');
            $table->dropIndex('idx_poruka_created_at');
        });

        Schema::table('datoteka', function (Blueprint $table) {
            $table->dropIndex('idx_datoteka_tip');
        });
    }
};

