<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'news',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid',
                )->unique();

                $table->foreignId(
                    'category_id',
                )
                    ->nullable()
                    ->constrained(
                        'news_categories',
                    )
                    ->nullOnDelete();

                $table->string(
                    'title',
                    255,
                );

                $table->string(
                    'slug',
                    255,
                )->unique();

                $table->text(
                    'summary',
                )->nullable();

                $table->longText(
                    'content',
                );

                /*
                 * References Media Library.
                 *
                 * If media is permanently deleted later,
                 * the news article remains valid.
                 */
                $table->foreignId(
                    'featured_image_id',
                )
                    ->nullable()
                    ->constrained(
                        'media_assets',
                    )
                    ->nullOnDelete();

                $table->string(
                    'status',
                    30,
                )
                    ->default(
                        'draft',
                    )
                    ->index();

                $table->boolean(
                    'is_featured',
                )
                    ->default(
                        false,
                    )
                    ->index();

                /*
                 * Public publication date.
                 */
                $table->timestamp(
                    'published_at',
                )
                    ->nullable()
                    ->index();

                /*
                 * Workflow timestamps.
                 */
                $table->timestamp(
                    'submitted_at',
                )->nullable();

                $table->timestamp(
                    'approved_at',
                )->nullable();

                $table->timestamp(
                    'archived_at',
                )->nullable();

                /*
                 * SEO.
                 */
                $table->string(
                    'seo_title',
                    255,
                )->nullable();

                $table->string(
                    'seo_description',
                    320,
                )->nullable();

                /*
                 * Ownership / workflow actors.
                 */
                $table->foreignId(
                    'created_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'submitted_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'approved_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'published_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'archived_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->timestamps();

                $table->softDeletes();

                /*
                 * Frequently-used admin/public queries.
                 */
                $table->index([
                    'status',
                    'published_at',
                ]);

                $table->index([
                    'category_id',
                    'status',
                ]);

                $table->index([
                    'is_featured',
                    'published_at',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'news',
        );
    }
};
