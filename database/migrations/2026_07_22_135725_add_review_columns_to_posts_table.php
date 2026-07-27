<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('org_unit_id')->nullable()->after('post_category_id')->constrained('org_units')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('org_unit_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('review_note');
        });
    }
};
