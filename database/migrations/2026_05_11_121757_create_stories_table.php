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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['news', 'stories']);
            $table->string('title');
            $table->string('author');
            $table->date('publish_date');
            $table->string('reading_time');
            $table->string('publication_image_path');
            $table->string('publication_video_path');
            $table->boolean('isArchive')->default(0)->nullable();
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
