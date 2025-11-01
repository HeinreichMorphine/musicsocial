<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ..._add_youtube_data_to_shares_table.php
    public function up(): void
    {
    Schema::table('shares', function (Blueprint $table) {
        $table->string('youtube_video_id')->nullable()->after('spotify_url');
        $table->string('youtube_url')->nullable()->after('youtube_video_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('shares', function (Blueprint $table) {
        $table->dropColumn(['youtube_video_id', 'youtube_url']);
    });
    }
};
