<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'media_variants',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('media_asset_id')
                    ->constrained('media_assets')
                    ->cascadeOnDelete();

                $table
                    ->string('name', 50);

                $table
                    ->string('disk', 50);

                $table
                    ->string('path', 1024);

                $table
                    ->string('mime_type', 127);

                $table
                    ->string('extension', 20);

                $table
                    ->unsignedInteger('width');

                $table
                    ->unsignedInteger('height');

                $table
                    ->unsignedBigInteger('size_bytes');

                $table
                    ->char('checksum', 64)
                    ->index();

                $table
                    ->timestamp('generated_at');

                $table->timestamps();

                $table->unique([
                    'media_asset_id',
                    'name',
                ]);

                $table->index([
                    'media_asset_id',
                    'name',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'media_variants',
        );
    }
};
