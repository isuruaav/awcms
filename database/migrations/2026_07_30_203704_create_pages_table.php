<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('excerpt')->nullable();

            /*
             * Content will contain sanitised page content.
             * Raw untrusted HTML must not be stored directly.
             */
            $table->longText('content')->nullable();

            /*
             * Reserved for the visual page builder.
             */
            $table->json('blocks')->nullable();

            $table->string('status', 32)
                ->default('draft')
                ->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'published_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
