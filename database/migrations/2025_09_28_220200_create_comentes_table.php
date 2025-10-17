<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentes', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->unsignedBigInteger('statute_id');
            $table->timestamps();
        });

        if (Schema::hasTable('statutes')) {
            Schema::table('comentes', function (Blueprint $table) {
                $table->foreign('statute_id')->references('id')->on('statutes')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comentes');
    }
};

