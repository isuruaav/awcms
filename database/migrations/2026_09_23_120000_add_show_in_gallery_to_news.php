<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->boolean('show_in_gallery')->default(false);
        });
        Schema::table('news_revisions', function (Blueprint $table): void {
            $table->boolean('show_in_gallery')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('news_revisions', function (Blueprint $table): void {
            $table->dropColumn('show_in_gallery');
        });
        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn('show_in_gallery');
        });
    }
};
