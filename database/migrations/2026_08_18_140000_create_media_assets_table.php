<?php

use App\Enums\MediaSource;
use App\Enums\MediaVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'media_assets',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique();

                /*
                 * Classification
                 */
                $table
                    ->string('type', 30)
                    ->index();

                $table
                    ->string('source', 20)
                    ->default(
                        MediaSource::Upload->value,
                    )
                    ->index();

                $table
                    ->string('visibility', 20)
                    ->default(
                        MediaVisibility::Public->value,
                    )
                    ->index();

                /*
                 * Human-readable information
                 */
                $table
                    ->string('title');

                $table
                    ->string('alt_text')
                    ->nullable();

                $table
                    ->text('caption')
                    ->nullable();

                /*
                 * Uploaded file storage information.
                 *
                 * These values are nullable because an
                 * external video/link does not have a
                 * locally stored file.
                 */
                $table
                    ->string('disk', 50)
                    ->nullable();

                $table
                    ->string('directory', 255)
                    ->nullable();

                $table
                    ->string('stored_name', 255)
                    ->nullable();

                $table
                    ->string('original_name', 255)
                    ->nullable();

                $table
                    ->string('path', 1024)
                    ->nullable();

                /*
                 * External media URL.
                 */
                $table
                    ->string('external_url', 2048)
                    ->nullable();

                /*
                 * Derived file metadata.
                 */
                $table
                    ->string('mime_type', 127)
                    ->nullable();

                $table
                    ->string('extension', 20)
                    ->nullable();

                $table
                    ->unsignedBigInteger('size_bytes')
                    ->nullable();

                $table
                    ->unsignedInteger('width')
                    ->nullable();

                $table
                    ->unsignedInteger('height')
                    ->nullable();

                /*
                 * SHA-256 checksum.
                 */
                $table
                    ->char('checksum', 64)
                    ->nullable()
                    ->index();

                /*
                 * Safe derived metadata only.
                 * Do not store sensitive EXIF information.
                 */
                $table
                    ->json('metadata')
                    ->nullable();

                /*
                 * Ownership / audit attribution.
                 */
                $table
                    ->foreignId('uploaded_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->softDeletes();

                $table->index([
                    'type',
                    'visibility',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'media_assets',
        );
    }
};
