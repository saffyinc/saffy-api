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
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('material')->nullable();
            $table->string('color')->nullable();
            $table->string('shape')->nullable();
            $table->string('size')->nullable();
            $table->string('weight')->nullable();
            $table->string('img_path');
            $table->boolean('isFeatured')->default(0);
            $table->boolean('isArchive')->default(0);
            $table->enum('category', ['fashion','gifts','home','kitchen','stationaries','supported','christmas','toys']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
