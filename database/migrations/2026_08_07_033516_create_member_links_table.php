<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // website|whatsapp|instagram|tiktok|youtube|linkedin|twitter
            $table->string('label', 50)->nullable(); // label custom, terutama website/whatsapp
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Pindahkan kontak lama dari kolom JSON members.social_links.
        $now = now();
        foreach (DB::table('members')->whereNotNull('social_links')->get(['id', 'social_links']) as $member) {
            $lama = json_decode((string) $member->social_links, true) ?: [];

            foreach (['website', 'whatsapp', 'linkedin'] as $i => $type) {
                if (! empty($lama[$type])) {
                    DB::table('member_links')->insert([
                        'member_id' => $member->id,
                        'type' => $type,
                        'label' => null,
                        'value' => $lama[$type],
                        'sort_order' => $i,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_links');
    }
};
