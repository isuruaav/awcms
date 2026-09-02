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
        Schema::table('news', function (Blueprint $table): void {
            $table->string('locale', 5)
                ->default('en')
                ->after('uuid')
                ->index();

            $table->uuid('translation_group')
                ->nullable()
                ->after('locale')
                ->index();

            $table->string('editor_mode', 20)
                ->default('visual')
                ->after('content');
        });

        DB::table('news')
            ->select(['id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $row): void {
                DB::table('news')
                    ->where('id', $row->id)
                    ->whereNull('translation_group')
                    ->update([
                        'translation_group' => (string) Str::uuid(),
                    ]);
            });

        Schema::table('news', function (Blueprint $table): void {
            $table->dropUnique('news_slug_unique');

            $table->unique(
                ['locale', 'slug'],
                'news_locale_slug_unique',
            );

            $table->unique(
                ['translation_group', 'locale'],
                'news_translation_group_locale_unique',
            );
        });

        Schema::table('news_revisions', function (Blueprint $table): void {
            $table->string('locale', 5)
                ->default('en')
                ->after('news_id');

            $table->uuid('translation_group')
                ->nullable()
                ->after('locale');

            $table->string('editor_mode', 20)
                ->default('visual')
                ->after('content');
        });

        DB::table('news_revisions')
            ->select(['id', 'news_id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $revision): void {
                $news = DB::table('news')
                    ->where('id', $revision->news_id)
                    ->first([
                        'locale',
                        'translation_group',
                        'editor_mode',
                    ]);

                if ($news === null) {
                    return;
                }

                DB::table('news_revisions')
                    ->where('id', $revision->id)
                    ->update([
                        'locale' => $news->locale,
                        'translation_group' => $news->translation_group,
                        'editor_mode' => $news->editor_mode,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('news_revisions', function (Blueprint $table): void {
            $table->dropColumn([
                'locale',
                'translation_group',
                'editor_mode',
            ]);
        });

        Schema::table('news', function (Blueprint $table): void {
            $table->dropUnique('news_translation_group_locale_unique');
            $table->dropUnique('news_locale_slug_unique');
            $table->unique('slug', 'news_slug_unique');

            $table->dropIndex(['locale']);
            $table->dropIndex(['translation_group']);

            $table->dropColumn([
                'locale',
                'translation_group',
                'editor_mode',
            ]);
        });
    }
};
