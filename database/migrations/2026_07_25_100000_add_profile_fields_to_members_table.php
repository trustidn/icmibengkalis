<?php

use App\Models\Member;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nia');
            $table->text('bio')->nullable()->after('expertise');
        });

        // Backfill slug anggota lama — trait HasSlug hanya generate slug saat create.
        Member::withTrashed()->whereNull('slug')->each(function (Member $member) {
            $base = Str::slug($member->full_name) ?: 'anggota';
            $slug = $base;
            $suffix = 2;

            while (Member::withTrashed()->where('slug', $slug)->where('id', '!=', $member->id)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $member->forceFill(['slug' => $slug])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['slug', 'bio']);
        });
    }
};
