<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->dropUnique('school_leaders_role_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->unique(
                'role_key',
                'school_leaders_role_key_unique',
            );
        });
    }
};
