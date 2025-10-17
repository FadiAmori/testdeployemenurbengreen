<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sub_category_id')) {
                $table->foreignId('sub_category_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('sub_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('product')->after('availability');
            }

            if (! Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sub_category_id')) {
                $table->dropConstrainedForeignId('sub_category_id');
            }

            if (Schema::hasColumn('products', 'image')) {
                $table->dropColumn('image');
            }

            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
