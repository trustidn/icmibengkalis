<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nia')->unique();
            $table->string('full_name');
            $table->string('title_prefix')->nullable();
            $table->string('title_suffix')->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('institution')->nullable();
            $table->foreignId('profession_id')->nullable()->constrained('professions')->nullOnDelete();
            $table->json('social_links')->nullable();
            $table->string('status')->default('aktif');
            $table->date('joined_at')->nullable();
            $table->boolean('show_contact_public')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
