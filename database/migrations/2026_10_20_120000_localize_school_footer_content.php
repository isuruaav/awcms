<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $layouts = DB::table('theme_layouts')
                ->where('theme_slug', 'school-of-signals')
                ->where('region', 'footer')
                ->where('locale', 'si')
                ->lockForUpdate()
                ->get();

            $english = DB::table('theme_layouts')
                ->where('theme_slug', 'school-of-signals')
                ->where('region', 'footer')
                ->where('locale', 'en')
                ->first();

            foreach ($layouts as $layout) {
                $updates = [];
                $revision = (int) $layout->revision_number;

                foreach (['draft_html' => 'draft', 'published_html' => 'published'] as $field => $status) {
                    $original = $layout->{$field};
                    if (! is_string($original) || $original === '') {
                        continue;
                    }

                    $html = $original;
                    $englishHtml = $english?->{$field};

                    // Keep the same quick links in both versions of the supplied footer.
                    if (str_contains($html, 'managed-school-footer') && is_string($englishHtml)
                        && preg_match('~<(?:section|nav) class="managed-school-footer-links">\s*<h2>.*?</h2>(.*?)</(?:section|nav)>~s', $englishHtml, $matches) === 1) {
                        $html = str_replace('[[primary_menu]]', $this->localizedLinks($matches[1]), $html);
                    }

                    $html = $this->translateFooter($html);
                    if ($html === $original) {
                        continue;
                    }

                    $cssField = $status === 'draft' ? 'draft_css' : 'published_css';

                    // Retain the original and corrected source in the existing revision history.
                    foreach ([$original, $html] as $snapshot) {
                        DB::table('theme_layout_revisions')->insert([
                            'theme_layout_id' => $layout->id,
                            'revision_number' => ++$revision,
                            'content_html' => $snapshot,
                            'content_css' => $layout->{$cssField},
                            'status' => $status,
                            'created_by' => null,
                            'created_at' => now(),
                        ]);
                    }

                    $updates[$field] = $html;
                }

                if ($updates !== []) {
                    DB::table('theme_layouts')->where('id', $layout->id)->update([
                        ...$updates,
                        'revision_number' => $revision,
                        'updated_at' => now(),
                    ]);
                }
            }

            // Fill only missing translations of the supplied school identity.
            foreach ([
                'site_name' => ['School of Signals', 'සංඥා පාසල'],
                'site_tagline' => ['Sri Lanka Signal Corps | School of Signals, Kandy', 'ශ්‍රී ලංකා සංඥා බලකාය | සංඥා පාසල, මහනුවර'],
                'address' => ['School Of Signals Sri Lanka Signal Corps, Boowelikada, Kandy, Sri Lanka.', 'සංඥා පාසල, ශ්‍රී ලංකා සංඥා බලකාය, බූවැලිකඩ, මහනුවර, ශ්‍රී ලංකාව.'],
            ] as $field => [$englishText, $sinhalaText]) {
                DB::table('site_settings')->where($field, $englishText)
                    ->where(fn ($query) => $query->whereNull($field.'_si')->orWhere($field.'_si', ''))
                    ->update([$field.'_si' => $sinhalaText]);
            }
        });
    }

    private function translateFooter(string $html): string
    {
        $translations = [
            'Sri Lanka Army' => 'ශ්‍රී ලංකා යුද්ධ හමුදාව',
            'Official information, training updates, publications and media from the Sri Lanka School of Signals.' => 'ශ්‍රී ලංකා සංඥා පාසලේ නිල තොරතුරු, පුහුණු යාවත්කාලීන, ප්‍රකාශන සහ මාධ්‍ය.',
            'Quick Links' => 'කඩිනම් සබැඳි',
            'Contact' => 'සම්බන්ධතා',
            'Follow Us' => 'අප සමඟ එක්වන්න',
            'Technological Sound' => 'තාක්ෂණයෙන් සවිමත්',
            'Home' => 'මුල් පිටුව',
            'About Us' => 'අප ගැන',
            'News' => 'පුවත්',
            'Gallery' => 'ඡායාරූප ගැලරිය',
            'Past Commandants' => 'හිටපු සේනාවිධායකවරු',
            'Past Chief Instructors' => 'හිටපු ප්‍රධාන උපදේශකවරු',
        ];

        return preg_replace_callback('~(?<=>)[^<]+(?=<)~u', static function (array $match) use ($translations): string {
            $text = preg_replace('/\s+/u', ' ', trim($match[0]));
            if (isset($translations[$text])) {
                return str_replace(trim($match[0]), $translations[$text], $match[0]);
            }

            return str_replace('All rights reserved.', 'සියලු හිමිකම් ඇවිරිණි.', $match[0]);
        }, $html) ?? $html;
    }

    private function localizedLinks(string $html): string
    {
        return preg_replace_callback('~\[\[site_url:(/[^\]]*)\]\]~', static function (array $match): string {
            $path = $match[1];
            if (preg_match('~^/en/pages/([^/]+)$~', $path, $pageMatch) === 1) {
                $englishPage = DB::table('pages')->where('locale', 'en')->where('slug', $pageMatch[1])->whereNull('deleted_at')->first();
                $translatedSlug = $englishPage && $englishPage->translation_group
                    ? DB::table('pages')->where('locale', 'si')->where('translation_group', $englishPage->translation_group)
                        ->whereNull('deleted_at')->where('status', 'published')->whereNotNull('published_at')
                        ->where('published_at', '<=', now())->value('slug')
                    : null;

                // Keep an English page usable if no published translation exists.
                $path = is_string($translatedSlug) ? '/si/pages/'.$translatedSlug : $path;
            } elseif ($path === '/') {
                $path = '/si';
            } elseif (str_starts_with($path, '/en/')) {
                $path = '/si/'.substr($path, 4);
            } elseif (in_array($path, ['/news', '/galleries', '/documents', '/contact'], true)) {
                $path = '/si'.$path;
            }

            return '[[site_url:'.$path.']]';
        }, $html) ?? $html;
    }

    public function down(): void
    {
        // Content corrections remain intact; original source is retained in layout revisions.
    }
};
