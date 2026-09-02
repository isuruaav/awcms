<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('editor_mode', 20)
                ->default('html')
                ->after('show_title');
        });

        Schema::table('page_revisions', function (Blueprint $table): void {
            $table->string('editor_mode', 20)
                ->default('html')
                ->after('show_title');
        });
    }

    public function down(): void
    {
        Schema::table('page_revisions', function (Blueprint $table): void {
            $table->dropColumn('editor_mode');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('editor_mode');
        });
    }
};
