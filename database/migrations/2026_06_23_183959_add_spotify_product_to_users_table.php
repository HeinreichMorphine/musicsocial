<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Uses hasColumn check to safely add spotify_product if the previous
     * migration was marked as run but the column was never actually created.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'spotify_product')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('spotify_product')->nullable()->after('spotify_refresh_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'spotify_product')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('spotify_product');
            });
        }
    }
};
