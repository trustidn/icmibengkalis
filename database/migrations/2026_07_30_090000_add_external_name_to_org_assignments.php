<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penugasan struktur organisasi untuk tokoh di luar sistem (mis. dewan penasehat
 * dari luar organisasi): member_id jadi nullable + kolom external_name sebagai
 * pengganti identitas. Tepat satu dari keduanya yang terisi (dijaga di form).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('org_assignments', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
            $table->string('external_name')->nullable()->after('member_id');
        });
    }

    public function down(): void
    {
        Schema::table('org_assignments', function (Blueprint $table) {
            $table->dropColumn('external_name');
            $table->foreignId('member_id')->nullable(false)->change();
        });
    }
};
