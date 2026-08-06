<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('display_name'); // "Ayşe & Mehmet K." gibi grup adı
            $table->string('invite_token', 32)->unique(); // /davet/{token} kişisel link
            $table->unsignedTinyInteger('max_guests')->default(1); // en fazla kaç kişi getirebilir
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
