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
        Schema::table('dislikes', function (Blueprint $table) {
            // 1. Drop foreign keys first so they don't depend on the index
            $table->dropForeign(['user_id']);
            $table->dropForeign(['share_id']);

            // 2. Drop the existing unique index
            $table->dropUnique('dislikes_user_id_share_id_unique');
            
            // 3. Add the composite primary key
            $table->primary(['user_id', 'share_id']);

            // 4. Re-add the foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('share_id')->references('id')->on('shares')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dislikes', function (Blueprint $table) {
            // 1. Drop foreign keys
            $table->dropForeign(['user_id']);
            $table->dropForeign(['share_id']);

            // 2. Drop primary key
            $table->dropPrimary(['user_id', 'share_id']);

            // 3. Restore unique index
            $table->unique(['user_id', 'share_id']);

            // 4. Restore foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('share_id')->references('id')->on('shares')->onDelete('cascade');
        });
    }
};
