<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->boolean('profile_enabled')->default(false);
            $table->longText('profile_html_en')->nullable();
            $table->longText('profile_html_si')->nullable();
            $table->text('profile_css')->nullable();
            $table->longText('profile_compiled_css')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_enabled', 'profile_html_en', 'profile_html_si',
                'profile_css', 'profile_compiled_css',
            ]);
        });
    }
};
