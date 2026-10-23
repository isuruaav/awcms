<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table): void {
            $table->string('locale', 2)->default('en')->after('title');
            $table->index(['locale', 'is_published', 'published_at'], 'publications_locale_published_index');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table): void {
            $table->dropIndex('publications_locale_published_index');
            $table->dropColumn('locale');
        });
    }
};
