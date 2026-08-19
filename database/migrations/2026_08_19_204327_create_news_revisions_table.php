<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_revisions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('news_id')
                ->constrained('news')
                ->cascadeOnDelete();

            $table->unsignedInteger('revision_number');

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('news_categories')
                ->nullOnDelete();

            $table->string('title', 255);

            $table->string('slug', 255);

            $table->text('summary')
                ->nullable();

            $table->longText('content');

            $table->foreignId('featured_image_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();

            $table->boolean('is_featured')
                ->default(false);

            $table->string('status', 30);

            $table->timestamp('published_at')
                ->nullable();

            $table->string('seo_title', 255)
                ->nullable();

            $table->string('seo_description', 320)
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('reason', 255)
                ->nullable();

            $table->timestamps();

            $table->unique([
                'news_id',
                'revision_number',
            ]);

            $table->index([
                'news_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'news_revisions',
        );
    }
};
