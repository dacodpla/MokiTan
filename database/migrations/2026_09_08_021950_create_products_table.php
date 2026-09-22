<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand');
            $table->unsignedBigInteger('price');
            $table->text('description')->nullable();
            $table->text('mold_history')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->json('specifications')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('is_featured');
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
