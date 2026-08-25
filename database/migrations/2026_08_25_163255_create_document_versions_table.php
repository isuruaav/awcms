<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'document_versions',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid',
                )->unique();

                $table->foreignId(
                    'document_id',
                )
                    ->constrained(
                        'documents',
                    )
                    ->cascadeOnDelete();

                /*
                 * PDF MediaAsset for this version.
                 */
                $table->foreignId(
                    'media_asset_id',
                )
                    ->constrained(
                        'media_assets',
                    )
                    ->restrictOnDelete();

                /*
                 * Sequential version number:
                 * 1, 2, 3, ...
                 */
                $table->unsignedInteger(
                    'version',
                );

                $table->string(
                    'version_label',
                    100,
                )->nullable();

                $table->text(
                    'change_note',
                )->nullable();

                /*
                 * User who uploaded/replaced this version.
                 */
                $table->foreignId(
                    'uploaded_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->timestamps();

                /*
                 * A document cannot have duplicate
                 * version numbers.
                 */
                $table->unique([
                    'document_id',
                    'version',
                ]);

                $table->index([
                    'document_id',
                    'created_at',
                ]);

                $table->index(
                    'media_asset_id',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_versions',
        );
    }
};
