<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->text('message')->nullable();
            $table->string('media_type')->nullable(); // photo, video, audio
            $table->string('media_path')->nullable(); // S3 path
            $table->boolean('is_approved')->default(true); // moderasyon istersen false yapıp panelden onaylat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
