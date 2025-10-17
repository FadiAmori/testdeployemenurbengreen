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
    if (!Schema::hasColumn('events', 'category_id')) {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
        });
    }
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn('category_id');
    });
}

};
