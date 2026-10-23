<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->longText('custom_css')->nullable();
        });

        Schema::table('page_revisions', function (Blueprint $table): void {
            $table->longText('custom_css')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_revisions', function (Blueprint $table): void {
            $table->dropColumn('custom_css');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('custom_css');
        });
    }
};
