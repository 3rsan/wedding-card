<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // ör: elena-marco -> /elena-marco
            $table->string('groom_name');
            $table->string('bride_name');
            $table->date('wedding_date');
            $table->string('theme')->default('mediterranean-summer'); // şablon adı
            $table->json('theme_colors')->nullable(); // {"primary": "#d4a04a", "text": "#2c3e50", "bg": "#f7f3eb"}
            $table->json('venues')->nullable(); // [{"type":"nikah","name":"Hotel Caruso","address":"...","lat":..,"lng":..,"time":"15:30"}, ...]
            $table->string('cover_image')->nullable();
            $table->string('hero_video')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete(); // ileride satış/panel için
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
