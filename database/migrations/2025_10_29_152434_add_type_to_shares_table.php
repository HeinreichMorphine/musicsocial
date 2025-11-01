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
        Schema::table('shares', function (Blueprint $table) {
            $table->string('type')->default('music')->after('user_id');
            $table->string('spotify_track_id')->nullable()->change();
            $table->string('youtube_video_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->dropColumn('type');
            // Revert nullable changes (assuming they were not nullable before)
            $table->string('spotify_track_id')->nullable(false)->change();
            $table->string('youtube_video_id')->nullable(false)->change();
        });
    }
};
