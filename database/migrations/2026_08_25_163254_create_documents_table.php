<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'documents',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid',
                )->unique();

                $table->string(
                    'title',
                    255,
                );

                /*
                 * Stable public URL.
                 *
                 * Replacing the PDF must not change this slug.
                 */
                $table->string(
                    'slug',
                    255,
                )->unique();

                $table->foreignId(
                    'document_category_id',
                )
                    ->nullable()
                    ->constrained(
                        'document_categories',
                    )
                    ->nullOnDelete();

                $table->text(
                    'description',
                )->nullable();

                $table->date(
                    'document_date',
                )
                    ->nullable()
                    ->index();

                /*
                 * Latest version number.
                 *
                 * The actual PDF MediaAsset is stored in
                 * document_versions.
                 */
                $table->unsignedInteger(
                    'current_version',
                )->default(
                    0,
                );

                /*
                 * Workflow
                 */
                $table->string(
                    'status',
                    30,
                )
                    ->default(
                        'draft',
                    )
                    ->index();

                $table->timestamp(
                    'published_at',
                )
                    ->nullable()
                    ->index();

                $table->timestamp(
                    'archived_at',
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
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
                |--------------------------------------------------------------------------
                | Audit Ownership
                |--------------------------------------------------------------------------
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

                $table->index([
                    'status',
                    'published_at',
                ]);

                $table->index([
                    'document_category_id',
                    'status',
                ]);

                $table->index([
                    'document_date',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'documents',
        );
    }
};
