<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_product_favorites', function (Blueprint $table) {
            // Add the missing foreign key columns
            if (!Schema::hasColumn('user_product_favorites', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('user_product_favorites', 'product_id')) {
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
            }
            
            // Add unique constraint if it doesn't exist
            $hasUnique = DB::table(DB::raw('information_schema.STATISTICS'))
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', 'user_product_favorites')
                ->where('INDEX_NAME', 'user_product_unique')
                ->exists();

            if (! $hasUnique) {
                $table->unique(['user_id', 'product_id'], 'user_product_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_product_favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['user_id', 'product_id']);
            $table->dropIndex('user_product_unique');
        });
    }
};
