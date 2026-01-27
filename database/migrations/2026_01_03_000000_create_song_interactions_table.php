<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('song_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('song_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['listen', 'like', 'dislike']);
            $table->timestamps();

            // Ensure a user tracks a song interaction uniquely? 
            // A user might listen multiple times? 
            // For "hiding" purposes, we just need to know if it exists.
            // Let's enforce unique per type? Or just unique user_song combo?
            // If I listen, then like, I update the type?
            // "Listened" is weaker than "Like".
            // If I "Like", it implies "Listened".
            // So we can update the record.
            $table->unique(['user_id', 'song_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_interactions');
    }
};
