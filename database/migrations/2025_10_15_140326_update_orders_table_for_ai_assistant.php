<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('EUR')->after('total_price');
            $table->text('notes')->nullable()->after('status');
        });

        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE orders MODIFY shipping_address TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'notes']);
        });

        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE orders MODIFY shipping_address VARCHAR(255) NOT NULL');
    }
};
