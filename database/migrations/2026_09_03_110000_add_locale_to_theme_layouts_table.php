<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'theme_layouts',
            function (Blueprint $table): void {
                /*
                 * Existing records automatically become English layouts.
                 */
                $table->string(
                    'locale',
                    5,
                )
                    ->default('en')
                    ->after('region');
            },
        );

        Schema::table(
            'theme_layouts',
            function (Blueprint $table): void {
                $table->dropUnique([
                    'theme_slug',
                    'region',
                ]);

                $table->unique([
                    'theme_slug',
                    'region',
                    'locale',
                ]);
            },
        );
    }

    public function down(): void
    {
        /*
         * The original schema only supports one layout per region.
         * Keep English records if this migration is rolled back.
         */
        DB::table('theme_layouts')
            ->where(
                'locale',
                '!=',
                'en',
            )
            ->delete();

        Schema::table(
            'theme_layouts',
            function (Blueprint $table): void {
                $table->dropUnique([
                    'theme_slug',
                    'region',
                    'locale',
                ]);

                $table->dropColumn(
                    'locale',
                );

                $table->unique([
                    'theme_slug',
                    'region',
                ]);
            },
        );
    }
};
