<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_expertises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('expertise_field_id')->constrained('expertise_fields')->cascadeOnDelete();
            $table->string('level')->default('pemula');
            $table->string('status')->default('diajukan');
            $table->timestamps();

            $table->unique(['member_id', 'expertise_field_id']);
            $table->index(['expertise_field_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_expertises');
    }
};
