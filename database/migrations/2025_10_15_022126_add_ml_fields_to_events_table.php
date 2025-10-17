<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('events', function (Blueprint $table) {
        if (!Schema::hasColumn('events', 'category')) {
            $table->string('category')->nullable()->after('title');
        }
        if (!Schema::hasColumn('events', 'description')) {
            $table->text('description')->nullable()->after('category');
        }
        if (!Schema::hasColumn('events', 'event_date')) {
            $table->dateTime('event_date')->after('description');
        }
        if (!Schema::hasColumn('events', 'is_published')) {
            $table->boolean('is_published')->default(true)->after('event_date');
        }
        if (!Schema::hasColumn('events', 'plant_step')) {
            $table->string('plant_step')->nullable()->after('is_published');
        }
        if (!Schema::hasColumn('events', 'user_id')) {
            $table->unsignedBigInteger('user_id')->nullable()->after('plant_step');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        }
    });
}

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['category', 'description', 'event_date', 'is_published', 'plant_step', 'user_id']);
        });
    }
};