<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'gallery_images',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'gallery_id',
                )
                    ->constrained(
                        'galleries',
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'media_asset_id',
                )
                    ->constrained(
                        'media_assets',
                    )
                    ->cascadeOnDelete();

                $table->string(
                    'caption',
                    1000,
                )->nullable();

                $table->string(
                    'alt_text',
                    255,
                )->nullable();

                $table->unsignedInteger(
                    'sort_order',
                )->default(
                    0,
                );

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

                $table->timestamps();

                $table->unique([
                    'gallery_id',
                    'media_asset_id',
                ]);

                $table->index([
                    'gallery_id',
                    'sort_order',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'gallery_images',
        );
    }
};
