<?php

use App\Services\CourseNavigation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('theme_layouts') || ! Schema::hasTable('theme_layout_revisions')) {
            return;
        }

        DB::transaction(function (): void {
            $headers = DB::table('theme_layouts')
                ->where('theme_slug', 'school-of-signals')
                ->where('region', 'header')
                ->whereIn('locale', ['en', 'si'])
                ->lockForUpdate()
                ->get();

            foreach ($headers as $header) {
                $revision = max(
                    (int) $header->revision_number,
                    (int) DB::table('theme_layout_revisions')
                        ->where('theme_layout_id', $header->id)
                        ->max('revision_number'),
                );
                $updates = [];

                foreach (['draft', 'published'] as $state) {
                    $field = $state.'_html';
                    $original = $header->{$field};
                    if (! is_string($original) || trim($original) === '') {
                        continue;
                    }

                    $updated = app(CourseNavigation::class)->updateHeader($original, $header->locale);
                    if ($updated === $original) {
                        continue;
                    }

                    foreach ([$original, $updated] as $snapshot) {
                        DB::table('theme_layout_revisions')->insert([
                            'theme_layout_id' => $header->id,
                            'revision_number' => ++$revision,
                            'content_html' => $snapshot,
                            'content_css' => $header->{$state.'_css'},
                            'status' => $state,
                            'created_by' => null,
                            'created_at' => now(),
                        ]);
                    }

                    $updates[$field] = $updated;
                }

                if ($updates !== []) {
                    DB::table('theme_layouts')
                        ->where('id', $header->id)
                        ->update([...$updates, 'revision_number' => $revision, 'updated_at' => now()]);
                }
            }
        });
    }

    public function down(): void
    {
        // Preserve subsequent header edits; original HTML is in revision history.
    }
};
