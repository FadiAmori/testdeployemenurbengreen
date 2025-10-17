<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('statutes') && ! Schema::hasColumn('statutes', 'photo')) {
            Schema::table('statutes', function (Blueprint $table) {
                $table->string('photo')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('statutes') && Schema::hasColumn('statutes', 'photo')) {
            Schema::table('statutes', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};

