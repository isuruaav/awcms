<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_leaders', function (Blueprint $table): void {
            $table->id();

            /*
             * Fixed role identifier:
             * commandant, chief_instructor, warrant_officer
             */
            $table->string('role_key', 50)->unique();

            $table->string('title_en', 120);
            $table->string('title_si', 160)->nullable();

            $table->string('name_en', 180)->nullable();
            $table->string('name_si', 220)->nullable();

            $table->foreignId('image_media_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->unsignedTinyInteger('sort_order')
                ->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_leaders');
    }
};
