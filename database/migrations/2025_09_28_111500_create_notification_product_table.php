<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_product')) {
            Schema::create('notification_product', function (Blueprint $table) {
                $table->id();
                // Ensure referenced tables exist by running this migration after them
                $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('notification_product', function (Blueprint $table) {
                if (!Schema::hasColumn('notification_product', 'notification_id')) {
                    $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
                } else {
                    $hasFk = DB::table(DB::raw('information_schema.KEY_COLUMN_USAGE'))
                        ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                        ->where('TABLE_NAME', 'notification_product')
                        ->where('COLUMN_NAME', 'notification_id')
                        ->whereNotNull('REFERENCED_TABLE_NAME')
                        ->exists();
                    if (! $hasFk) {
                        $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
                    }
                }

                if (!Schema::hasColumn('notification_product', 'product_id')) {
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                } else {
                    $hasFk = DB::table(DB::raw('information_schema.KEY_COLUMN_USAGE'))
                        ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                        ->where('TABLE_NAME', 'notification_product')
                        ->where('COLUMN_NAME', 'product_id')
                        ->whereNotNull('REFERENCED_TABLE_NAME')
                        ->exists();
                    if (! $hasFk) {
                        $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_product');
    }
};
