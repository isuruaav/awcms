<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hero_slide_translations',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('hero_slide_id')
                    ->constrained('hero_slides')
                    ->cascadeOnDelete();

                $table->string('locale', 5);
                $table->string('title', 180);
                $table->string('subtitle', 255)->nullable();
                $table->string('button_label', 100)->nullable();
                $table->string('button_url', 2048)->nullable();
                $table->timestamps();

                $table->unique(
                    ['hero_slide_id', 'locale'],
                    'hero_slide_translations_slide_locale_unique',
                );

                $table->index(
                    'locale',
                    'hero_slide_translations_locale_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slide_translations');
    }
};
