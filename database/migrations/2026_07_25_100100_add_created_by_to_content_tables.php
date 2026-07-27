<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak kepemilikan untuk Pengumuman, Agenda, dan Galeri — agar peran
 * admin-divisi/ketua-divisi hanya bisa mengelola konten buatan sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
