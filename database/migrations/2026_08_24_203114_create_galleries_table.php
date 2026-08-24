<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'galleries',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid',
                )->unique();

                $table->string(
                    'title',
                    255,
                );

                $table->string(
                    'slug',
                    255,
                )->unique();

                $table->date(
                    'event_date',
                )
                    ->nullable()
                    ->index();

                $table->text(
                    'description',
                )->nullable();

                $table->foreignId(
                    'cover_media_id',
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
                    'event_date',
                    'status',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'galleries',
        );
    }
};
