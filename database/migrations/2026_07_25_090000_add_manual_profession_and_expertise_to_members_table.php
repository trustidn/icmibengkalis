<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profesi & bidang keahlian anggota untuk saat ini cukup diisi manual (teks bebas)
 * pada form anggota, tanpa terikat ke master data `professions`/`expertise_fields`.
 * Tabel master data & relasinya TETAP dipertahankan untuk dipakai lagi oleh
 * Direktori Kepakaran di Fase 4 — lihat docs/06-roadmap.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('profession_id');
            $table->text('expertise')->nullable()->after('profession');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['profession', 'expertise']);
        });
    }
};
