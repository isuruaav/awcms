<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->string('rank', 80)
                ->nullable()
                ->after('role_key');

            $table->date('start_date')
                ->nullable()
                ->after('rank');

            $table->date('end_date')
                ->nullable()
                ->after('start_date');

            $table->index(
                ['role_key', 'start_date'],
                'school_leaders_role_start_idx',
            );

            $table->index(
                ['role_key', 'end_date'],
                'school_leaders_role_end_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->dropIndex('school_leaders_role_start_idx');
            $table->dropIndex('school_leaders_role_end_idx');

            $table->dropColumn([
                'rank',
                'start_date',
                'end_date',
            ]);
        });
    }
};
