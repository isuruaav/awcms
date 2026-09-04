<?php

namespace App\Services;

use App\Enums\ThemeLayoutLocale;
use App\Enums\ThemeLayoutRegion;
use App\Enums\ThemeLayoutStatus;
use App\Models\ThemeLayout;
use App\Models\ThemeLayoutRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ThemeLayoutService
{
    public function __construct(
        private readonly ThemeManager $themeManager,
        private readonly ThemeLayoutSanitizer $sanitizer,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function saveDraft(
        string $themeSlug,
        ThemeLayoutRegion $region,
        ThemeLayoutLocale $locale,
        User $actor,
        ?string $html,
        ?string $css,
    ): ThemeLayout {
        Gate::forUser($actor)->authorize('theme-layouts.manage');

        $themeSlug = $this->validatedThemeSlug($themeSlug);
        $safeHtml = $this->sanitizer->sanitizeHtml($html);
        $safeCss = $this->sanitizer->sanitizeCss($css);

        if ($safeHtml === '') {
            throw ValidationException::withMessages([
                'layout_html' => 'Header or footer HTML is required.',
            ]);
        }

        return DB::transaction(function () use (
            $themeSlug,
            $region,
            $locale,
            $actor,
            $safeHtml,
            $safeCss,
        ): ThemeLayout {
            $layout = ThemeLayout::query()
                ->where('theme_slug', $themeSlug)
                ->where('region', $region->value)
                ->where('locale', $locale->value)
                ->lockForUpdate()
                ->first();

            if (! $layout instanceof ThemeLayout) {
                $layout = ThemeLayout::query()->create([
                    'theme_slug' => $themeSlug,
                    'region' => $region->value,
                    'locale' => $locale->value,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);
            }

            $oldHtml = $layout->draft_html;
            $oldCss = $layout->draft_css;
            $previousRevision = $layout->revision_number;
            $nextRevision = $previousRevision + 1;

            $layout->forceFill([
                'draft_html' => $safeHtml,
                'draft_css' => $safeCss,
                'revision_number' => $nextRevision,
                'updated_by' => $actor->id,
            ])->save();

            ThemeLayoutRevision::query()->create([
                'theme_layout_id' => $layout->id,
                'revision_number' => $nextRevision,
                'content_html' => $safeHtml,
                'content_css' => $safeCss,
                'status' => ThemeLayoutStatus::Draft->value,
                'created_by' => $actor->id,
            ]);

            $this->auditLogger->log(
                event: 'theme-layouts.draft-saved',
                description: $region->label().' '.$locale->label().' layout draft was saved.',
                actor: $actor,
                subject: $layout,
                oldValues: $this->auditSummary(
                    $oldHtml,
                    $oldCss,
                    $previousRevision,
                ),
                newValues: $this->auditSummary(
                    $safeHtml,
                    $safeCss,
                    $nextRevision,
                ),
            );

            return $layout->refresh();
        }, 3);
    }

    public function publish(ThemeLayout $layout, User $actor): ThemeLayout
    {
        Gate::forUser($actor)->authorize('theme-layouts.manage');

        return DB::transaction(function () use ($layout, $actor): ThemeLayout {
            $locked = ThemeLayout::query()
                ->lockForUpdate()
                ->findOrFail($layout->id);

            if (! is_string($locked->draft_html) || trim($locked->draft_html) === '') {
                throw ValidationException::withMessages([
                    'layout_html' => 'Save a valid draft before publishing.',
                ]);
            }

            $nextRevision = $locked->revision_number + 1;

            $locked->forceFill([
                'published_html' => $locked->draft_html,
                'published_css' => $locked->draft_css,
                'status' => ThemeLayoutStatus::Published->value,
                'revision_number' => $nextRevision,
                'published_at' => now(),
                'published_by' => $actor->id,
                'updated_by' => $actor->id,
            ])->save();

            ThemeLayoutRevision::query()->create([
                'theme_layout_id' => $locked->id,
                'revision_number' => $nextRevision,
                'content_html' => $locked->published_html,
                'content_css' => $locked->published_css,
                'status' => ThemeLayoutStatus::Published->value,
                'created_by' => $actor->id,
            ]);

            $this->auditLogger->log(
                event: 'theme-layouts.published',
                description: $locked->region->label().' '.$locked->locale->label().' layout was published.',
                actor: $actor,
                subject: $locked,
                newValues: $this->auditSummary(
                    $locked->published_html,
                    $locked->published_css,
                    $nextRevision,
                ),
            );

            return $locked->refresh();
        }, 3);
    }

    private function validatedThemeSlug(string $themeSlug): string
    {
        $themeSlug = trim($themeSlug);

        if ($themeSlug === '' || $this->themeManager->find($themeSlug) === null) {
            throw ValidationException::withMessages([
                'theme' => 'The selected theme is not installed.',
            ]);
        }

        return $themeSlug;
    }

    /**
     * Avoid copying full layout source into the general audit JSON column.
     * Immutable source snapshots are stored in theme_layout_revisions.
     *
     * @return array<string, int|string>
     */
    private function auditSummary(?string $html, ?string $css, int $revision): array
    {
        $html ??= '';
        $css ??= '';

        return [
            'revision_number' => $revision,
            'html_length' => mb_strlen($html),
            'css_length' => mb_strlen($css),
            'html_sha256' => hash('sha256', $html),
            'css_sha256' => hash('sha256', $css),
        ];
    }
}
