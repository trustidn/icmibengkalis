<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            // "user:{id}" untuk anggota login, "guest:{token-cookie}" untuk pengunjung —
            // satu apresiasi per orang per artikel.
            $table->string('liker_key', 64);
            $table->timestamps();

            $table->unique(['post_id', 'liker_key']);
        });

        // Counter denormalisasi agar kartu artikel tidak perlu join/count.
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('view_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
