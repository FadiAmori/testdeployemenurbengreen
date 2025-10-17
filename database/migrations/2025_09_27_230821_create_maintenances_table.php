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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->unique();
            $table->text('description')->nullable();
            $table->json('steps')->nullable(); // JSON array of steps with title and description
            $table->string('photo')->nullable();
            $table->string('video')->nullable();
            $table->foreignId('material_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('optional_id')->nullable()->constrained('products')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};