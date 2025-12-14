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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('email');
            $table->string('spotify_id')->nullable()->after('google_id');
            $table->text('spotify_token')->nullable()->after('spotify_id');
            $table->text('spotify_refresh_token')->nullable()->after('spotify_token');
            $table->string('avatar')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'spotify_id',
                'spotify_token',
                'spotify_refresh_token',
                'avatar',
            ]);
        });
    }
};
