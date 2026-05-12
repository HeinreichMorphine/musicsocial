<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('song_interactions', function (Blueprint $table) {
            // Using DB::statement because change() on enums is not supported by all drivers
            DB::statement("ALTER TABLE song_interactions MODIFY COLUMN type ENUM('listen', 'like', 'dislike', 'share')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('song_interactions', function (Blueprint $table) {
            DB::statement("ALTER TABLE song_interactions MODIFY COLUMN type ENUM('listen', 'like', 'dislike')");
        });
    }
};
