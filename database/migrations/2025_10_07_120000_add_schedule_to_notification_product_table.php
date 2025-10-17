<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_product', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_product', 'days')) {
                // store as JSON for easier querying
                $table->json('days')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('notification_product', 'time')) {
                $table->time('time')->nullable()->after('days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_product', function (Blueprint $table) {
            if (Schema::hasColumn('notification_product', 'time')) {
                $table->dropColumn('time');
            }
            if (Schema::hasColumn('notification_product', 'days')) {
                $table->dropColumn('days');
            }
        });
    }
};
