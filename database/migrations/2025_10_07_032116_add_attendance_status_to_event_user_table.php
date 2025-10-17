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
        Schema::table('event_user', function (Blueprint $table) {
            $table->string('attendance_status')->nullable()->after('user_id');
            // Optional: you can use default('pending') instead of nullable()
            // $table->string('attendance_status')->default('pending')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn('attendance_status');
        });
    }
};
