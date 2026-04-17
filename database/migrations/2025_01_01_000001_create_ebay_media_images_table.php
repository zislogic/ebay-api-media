<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebay_media_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('credential_id');
            $table->string('image_id', 255)->nullable();
            $table->string('image_url', 1000);
            $table->string('max_dimension_image_url', 1000)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('source_type', 10); // 'file' or 'url'
            $table->string('source_path', 1000);
            $table->timestamp('expiration_date')->nullable();
            $table->timestamps();

            $table->index('credential_id', 'media_images_credential_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebay_media_images');
    }
};
