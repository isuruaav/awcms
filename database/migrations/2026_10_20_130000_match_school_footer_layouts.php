<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $english = DB::table('theme_layouts')
                ->where('theme_slug', 'school-of-signals')
                ->where('region', 'footer')
                ->where('locale', 'en')
                ->first();

            $sinhala = DB::table('theme_layouts')
                ->where('theme_slug', 'school-of-signals')
                ->where('region', 'footer')
                ->where('locale', 'si')
                ->lockForUpdate()
                ->first();

            if ($english === null || $sinhala === null) {
                return;
            }

            $updates = [];
            $revision = (int) $sinhala->revision_number;

            foreach (['draft' => 'draft', 'published' => 'published'] as $prefix => $status) {
                $htmlField = $prefix.'_html';
                $cssField = $prefix.'_css';
                $html = $sinhala->{$htmlField};
                $css = $english->{$cssField};

                if (! is_string($html) || ! str_contains($html, 'managed-school-footer')
                    || ! is_string($css) || trim($css) === '') {
                    continue;
                }

                // Match the English structure while retaining Sinhala text and URLs.
                $html = preg_replace(
                    '~<div class="managed-school-footer-brand">(.*?)</div>~s',
                    '<a class="managed-school-footer-brand" href="[[site_url:/si]]">$1</a>',
                    $html,
                ) ?? $html;
                $html = preg_replace(
                    '~<section class="managed-school-footer-links">(.*?)</section>~s',
                    '<nav class="managed-school-footer-links">$1</nav>',
                    $html,
                ) ?? $html;

                if ($html === $sinhala->{$htmlField} && $css === $sinhala->{$cssField}) {
                    continue;
                }

                foreach ([
                    [$sinhala->{$htmlField}, $sinhala->{$cssField}],
                    [$html, $css],
                ] as [$snapshotHtml, $snapshotCss]) {
                    DB::table('theme_layout_revisions')->insert([
                        'theme_layout_id' => $sinhala->id,
                        'revision_number' => ++$revision,
                        'content_html' => $snapshotHtml,
                        'content_css' => $snapshotCss,
                        'status' => $status,
                        'created_by' => null,
                        'created_at' => now(),
                    ]);
                }

                $updates[$htmlField] = $html;
                $updates[$cssField] = $css;
            }

            if ($updates !== []) {
                DB::table('theme_layouts')->where('id', $sinhala->id)->update([
                    ...$updates,
                    'revision_number' => $revision,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Preserve subsequent edits; original layouts remain available in revision history.
    }
};
