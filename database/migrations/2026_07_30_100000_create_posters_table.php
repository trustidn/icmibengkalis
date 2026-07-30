<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Poster ucapan di beranda (hari jadi, kemerdekaan, dll.) — gambar via Media Library
 * collection "image". Masa tayang opsional: di luar rentang starts_at..ends_at poster
 * otomatis tidak tampil; section beranda disembunyikan bila tak ada poster aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('link_url')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posters');
    }
};
