<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_layouts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('theme_slug', 100);
            $table->string('region', 20);
            $table->longText('draft_html')->nullable();
            $table->longText('draft_css')->nullable();
            $table->longText('published_html')->nullable();
            $table->longText('published_css')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['theme_slug', 'region']);
            $table->index(['theme_slug', 'status']);
        });

        Schema::create('theme_layout_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('theme_layout_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->longText('content_html')->nullable();
            $table->longText('content_css')->nullable();
            $table->string('status', 20);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['theme_layout_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_layout_revisions');
        Schema::dropIfExists('theme_layouts');
    }
};
