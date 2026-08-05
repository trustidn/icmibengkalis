<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kegiatan ber-ID card — entitas tersendiri, terpisah dari Agenda/Event.
        // Setiap anggota aktif OTOMATIS memiliki ID card untuk tiap kegiatan
        // yang dibuka — tidak ada pendaftaran.
        Schema::create('id_card_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('event_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_events');
    }
};
