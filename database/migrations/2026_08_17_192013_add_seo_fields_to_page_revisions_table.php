<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'page_revisions',
            function (Blueprint $table): void {
                $table
                    ->string('seo_title', 70)
                    ->nullable()
                    ->after('content');

                $table
                    ->string('meta_description', 160)
                    ->nullable()
                    ->after('seo_title');

                $table
                    ->string('canonical_url', 2048)
                    ->nullable()
                    ->after('meta_description');

                $table
                    ->boolean('robots_index')
                    ->default(true)
                    ->after('canonical_url');

                $table
                    ->string('og_title', 95)
                    ->nullable()
                    ->after('robots_index');

                $table
                    ->string('og_description', 200)
                    ->nullable()
                    ->after('og_title');

                $table
                    ->string('og_image', 2048)
                    ->nullable()
                    ->after('og_description');
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'page_revisions',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'seo_title',
                    'meta_description',
                    'canonical_url',
                    'robots_index',
                    'og_title',
                    'og_description',
                    'og_image',
                ]);
            },
        );
    }
};
