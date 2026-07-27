<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `youtube_url` diganti nama jadi `video_url` (sekarang mendukung YouTube DAN
 * Vimeo, bukan cuma YouTube) + kolom baru `video_provider` dan `thumbnail_url`
 * (thumbnail video diturunkan/di-fetch sekali saat item ditambahkan, bukan
 * dihitung ulang setiap render — lihat App\Support\VideoUrl & GalleryService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('album_items', function (Blueprint $table) {
            $table->renameColumn('youtube_url', 'video_url');
        });

        Schema::table('album_items', function (Blueprint $table) {
            $table->string('video_provider')->nullable()->after('video_url');
            $table->string('thumbnail_url')->nullable()->after('video_provider');
        });
    }

    public function down(): void
    {
        Schema::table('album_items', function (Blueprint $table) {
            $table->dropColumn(['video_provider', 'thumbnail_url']);
        });

        Schema::table('album_items', function (Blueprint $table) {
            $table->renameColumn('video_url', 'youtube_url');
        });
    }
};
