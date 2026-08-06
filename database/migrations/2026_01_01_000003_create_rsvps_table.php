<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->boolean('attending');
            $table->unsignedTinyInteger('guest_count')->default(1);
            $table->json('attendee_names')->nullable(); // ["Ayşe Kaya", "Mehmet Kaya"]
            $table->text('note')->nullable();
            $table->timestamps();

            // bir misafir grubu birden fazla kez cevap verebilir (güncelleme), en son kayıt geçerli sayılır
            $table->index(['guest_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
