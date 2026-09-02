<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'pages',
            function (Blueprint $table): void {
                $table
                    ->string('locale', 5)
                    ->default('en')
                    ->after('slug')
                    ->index();

                $table
                    ->uuid('translation_group')
                    ->nullable()
                    ->after('locale')
                    ->index();
            },
        );

        DB::table('pages')
            ->select('id')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($pages): void {
                    foreach ($pages as $page) {
                        $pageId = (int) data_get(
                            $page,
                            'id',
                        );

                        if ($pageId < 1) {
                            continue;
                        }

                        DB::table('pages')
                            ->where('id', $pageId)
                            ->update([
                                'translation_group' => (string) Str::uuid(),
                            ]);
                    }
                },
            );

        Schema::table(
            'pages',
            function (Blueprint $table): void {
                $table->dropUnique('pages_slug_unique');

                $table->unique(
                    [
                        'locale',
                        'slug',
                    ],
                    'pages_locale_slug_unique',
                );

                $table->unique(
                    [
                        'translation_group',
                        'locale',
                    ],
                    'pages_translation_group_locale_unique',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'pages',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'pages_translation_group_locale_unique',
                );

                $table->dropUnique(
                    'pages_locale_slug_unique',
                );

                $table->unique(
                    'slug',
                    'pages_slug_unique',
                );

                $table->dropIndex([
                    'locale',
                ]);

                $table->dropIndex([
                    'translation_group',
                ]);

                $table->dropColumn([
                    'locale',
                    'translation_group',
                ]);
            },
        );
    }
};
