<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;
use App\Services\PageRevisionService;
use App\Support\PageSlugger;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PageEdit extends Component
{
    #[Locked]
    public int $pageId;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public string $seoTitle = '';

    public string $metaDescription = '';

    public string $canonicalUrl = '';

    public bool $robotsIndex = true;

    public string $ogTitle = '';

    public string $ogDescription = '';

    public string $ogImage = '';

    public bool $slugManuallyEdited = true;

    public function mount(Page $page): void
    {
        Gate::authorize(
            'pages.update',
        );

        $this->ensureDraftPage(
            $page,
        );

        $this->pageId =
            (int) $page->id;

        $this->title =
            (string) $page->title;

        $this->slug =
            (string) $page->slug;

        $this->excerpt =
            is_string($page->excerpt)
                ? $page->excerpt
                : '';

        $this->content =
            is_string($page->content)
                ? $page->content
                : '';

        /*
         * SEO
         */
        $this->seoTitle =
            is_string($page->seo_title)
                ? $page->seo_title
                : '';

        $this->metaDescription =
            is_string($page->meta_description)
                ? $page->meta_description
                : '';

        $this->canonicalUrl =
            is_string($page->canonical_url)
                ? $page->canonical_url
                : '';

        $this->robotsIndex =
            (bool) $page->robots_index;

        $this->ogTitle =
            is_string($page->og_title)
                ? $page->og_title
                : '';

        $this->ogDescription =
            is_string($page->og_description)
                ? $page->og_description
                : '';

        $this->ogImage =
            is_string($page->og_image)
                ? $page->og_image
                : '';

        /*
         * Existing page URLs should not automatically
         * change when its title changes.
         */
        $this->slugManuallyEdited = true;
    }

    public function updatedTitle(): void
    {
        if ($this->slugManuallyEdited) {
            return;
        }

        $this->slug = Str::slug(
            $this->title,
        );
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;

        $this->slug = Str::slug(
            $this->slug,
        );
    }

    public function regenerateSlug(): void
    {
        $this->slugManuallyEdited = false;

        $this->slug = Str::slug(
            $this->title,
        );

        $this->resetValidation(
            'slug',
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'pages.update',
        );

        $actor = $this->actor();
        $page = $this->page();

        $this->ensureDraftPage(
            $page,
        );

        $this->normaliseInput();
        $this->validate();

        $slugSource = $this->slug !== ''
            ? $this->slug
            : $this->title;

        $uniqueSlug = PageSlugger::unique(
            $slugSource,
            $page->id,
        );

        /*
         * Existing values.
         */
        $oldTitle =
            (string) $page->title;

        $oldSlug =
            (string) $page->slug;

        $oldExcerpt =
            is_string($page->excerpt)
                ? $page->excerpt
                : '';

        $oldContent =
            is_string($page->content)
                ? $page->content
                : '';

        $oldSeoTitle =
            $this->stringValue(
                $page->seo_title,
            );

        $oldMetaDescription =
            $this->stringValue(
                $page->meta_description,
            );

        $oldCanonicalUrl =
            $this->stringValue(
                $page->canonical_url,
            );

        $oldRobotsIndex =
            (bool) $page->robots_index;

        $oldOgTitle =
            $this->stringValue(
                $page->og_title,
            );

        $oldOgDescription =
            $this->stringValue(
                $page->og_description,
            );

        $oldOgImage =
            $this->stringValue(
                $page->og_image,
            );

        /*
         * Change detection.
         */
        $titleChanged =
            $oldTitle !== $this->title;

        $slugChanged =
            $oldSlug !== $uniqueSlug;

        $excerptChanged =
            $oldExcerpt !== $this->excerpt;

        $contentChanged =
            $oldContent !== $this->content;

        $seoTitleChanged =
            $oldSeoTitle !== $this->seoTitle;

        $metaDescriptionChanged =
            $oldMetaDescription
            !== $this->metaDescription;

        $canonicalUrlChanged =
            $oldCanonicalUrl
            !== $this->canonicalUrl;

        $robotsIndexChanged =
            $oldRobotsIndex
            !== $this->robotsIndex;

        $ogTitleChanged =
            $oldOgTitle !== $this->ogTitle;

        $ogDescriptionChanged =
            $oldOgDescription
            !== $this->ogDescription;

        $ogImageChanged =
            $oldOgImage !== $this->ogImage;

        $hasChanges =
            $titleChanged
            || $slugChanged
            || $excerptChanged
            || $contentChanged
            || $seoTitleChanged
            || $metaDescriptionChanged
            || $canonicalUrlChanged
            || $robotsIndexChanged
            || $ogTitleChanged
            || $ogDescriptionChanged
            || $ogImageChanged;

        if (! $hasChanges) {
            session()->flash(
                'status',
                'No page changes were detected.',
            );

            return;
        }

        DB::transaction(
            function () use (
                $actor,
                $page,
                $uniqueSlug,
                $oldTitle,
                $oldSlug,
                $oldExcerpt,
                $oldContent,
                $oldSeoTitle,
                $oldMetaDescription,
                $oldCanonicalUrl,
                $oldRobotsIndex,
                $oldOgTitle,
                $oldOgDescription,
                $oldOgImage,
                $titleChanged,
                $slugChanged,
                $excerptChanged,
                $contentChanged,
                $seoTitleChanged,
                $metaDescriptionChanged,
                $canonicalUrlChanged,
                $robotsIndexChanged,
                $ogTitleChanged,
                $ogDescriptionChanged,
                $ogImageChanged,
            ): void {
                $page->forceFill([
                    'title' => $this->title,

                    'slug' => $uniqueSlug,

                    'excerpt' => $this->excerpt !== ''
                            ? $this->excerpt
                            : null,

                    'content' => $this->content !== ''
                            ? $this->content
                            : null,

                    /*
                     * SEO
                     */
                    'seo_title' => $this->seoTitle !== ''
                            ? $this->seoTitle
                            : null,

                    'meta_description' => $this->metaDescription !== ''
                            ? $this->metaDescription
                            : null,

                    'canonical_url' => $this->canonicalUrl !== ''
                            ? $this->canonicalUrl
                            : null,

                    'robots_index' => $this->robotsIndex,

                    'og_title' => $this->ogTitle !== ''
                            ? $this->ogTitle
                            : null,

                    'og_description' => $this->ogDescription !== ''
                            ? $this->ogDescription
                            : null,

                    'og_image' => $this->ogImage !== ''
                            ? $this->ogImage
                            : null,

                    'updated_by' => $actor->id,
                ])->save();

                /**
                 * @var array<string, mixed> $oldValues
                 */
                $oldValues = [];

                /**
                 * @var array<string, mixed> $newValues
                 */
                $newValues = [];

                if ($titleChanged) {
                    $oldValues['title'] =
                        $oldTitle;

                    $newValues['title'] =
                        $this->title;
                }

                if ($slugChanged) {
                    $oldValues['slug'] =
                        $oldSlug;

                    $newValues['slug'] =
                        $uniqueSlug;
                }

                if ($excerptChanged) {
                    $oldValues['excerpt_length'] =
                        mb_strlen($oldExcerpt);

                    $newValues['excerpt_length'] =
                        mb_strlen(
                            $this->excerpt,
                        );

                    $newValues['excerpt_changed'] =
                        true;
                }

                if ($contentChanged) {
                    $oldValues['content_length'] =
                        mb_strlen($oldContent);

                    $newValues['content_length'] =
                        mb_strlen(
                            $this->content,
                        );

                    $newValues['content_changed'] =
                        true;
                }

                /*
                 * SEO audit values do not store full
                 * descriptions or page content.
                 */
                if ($seoTitleChanged) {
                    $oldValues['seo_title_present'] =
                        $oldSeoTitle !== '';

                    $newValues['seo_title_present'] =
                        $this->seoTitle !== '';

                    $newValues['seo_title_changed'] =
                        true;
                }

                if ($metaDescriptionChanged) {
                    $oldValues[
                        'meta_description_present'
                    ] = $oldMetaDescription !== '';

                    $newValues[
                        'meta_description_present'
                    ] = $this->metaDescription !== '';

                    $newValues[
                        'meta_description_changed'
                    ] = true;
                }

                if ($canonicalUrlChanged) {
                    $oldValues[
                        'canonical_url_present'
                    ] = $oldCanonicalUrl !== '';

                    $newValues[
                        'canonical_url_present'
                    ] = $this->canonicalUrl !== '';

                    $newValues[
                        'canonical_url_changed'
                    ] = true;
                }

                if ($robotsIndexChanged) {
                    $oldValues['robots_index'] =
                        $oldRobotsIndex;

                    $newValues['robots_index'] =
                        $this->robotsIndex;
                }

                if ($ogTitleChanged) {
                    $oldValues['og_title_present'] =
                        $oldOgTitle !== '';

                    $newValues['og_title_present'] =
                        $this->ogTitle !== '';

                    $newValues['og_title_changed'] =
                        true;
                }

                if ($ogDescriptionChanged) {
                    $oldValues[
                        'og_description_present'
                    ] = $oldOgDescription !== '';

                    $newValues[
                        'og_description_present'
                    ] = $this->ogDescription !== '';

                    $newValues[
                        'og_description_changed'
                    ] = true;
                }

                if ($ogImageChanged) {
                    $oldValues['og_image_present'] =
                        $oldOgImage !== '';

                    $newValues['og_image_present'] =
                        $this->ogImage !== '';

                    $newValues['og_image_changed'] =
                        true;
                }

                app(AuditLogger::class)->log(
                    event: 'pages.updated',
                    description: 'Draft website page updated.',
                    actor: $actor,
                    subject: $page,
                    oldValues: $oldValues,
                    newValues: $newValues,
                );

                app(PageRevisionService::class)
                    ->capture(
                        page: $page,
                        actor: $actor,
                        summary: 'Draft page content and metadata updated.',
                    );
            },
        );

        session()->flash(
            'status',
            "{$this->title} was updated successfully.",
        );

        $this->redirectRoute(
            'admin.pages.index',
            navigate: true,
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

                Rule::unique(
                    'pages',
                    'slug',
                )->ignore(
                    $this->pageId,
                ),
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content' => [
                'nullable',
                'string',
                'max:100000',
            ],

            /*
             * SEO
             */
            'seoTitle' => [
                'nullable',
                'string',
                'max:70',
            ],

            'metaDescription' => [
                'nullable',
                'string',
                'max:160',
            ],

            'canonicalUrl' => [
                'nullable',
                'string',
                'max:2048',
                'url:http,https',
            ],

            'robotsIndex' => [
                'boolean',
            ],

            'ogTitle' => [
                'nullable',
                'string',
                'max:95',
            ],

            'ogDescription' => [
                'nullable',
                'string',
                'max:200',
            ],

            'ogImage' => [
                'nullable',
                'string',
                'max:2048',
                'url:http,https',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'slug.regex' => 'The slug may contain lowercase letters, numbers and hyphens only.',

            'slug.unique' => 'This slug is already used by another page.',

            'canonicalUrl.url' => 'The canonical URL must be a valid HTTP or HTTPS URL.',

            'ogImage.url' => 'The Open Graph image must be a valid HTTP or HTTPS URL.',
        ];
    }

    public function render(): View
    {
        $page = $this->page();

        return view(
            'livewire.admin.pages.page-edit',
            compact('page'),
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit Page',
            ],
        );
    }

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }

    private function page(): Page
    {
        return Page::query()
            ->findOrFail(
                $this->pageId,
            );
    }

    private function ensureDraftPage(
        Page $page,
    ): void {
        $status = $page->getRawOriginal(
            'status',
        );

        abort_unless(
            $status === PageStatus::Draft->value,
            409,
            'Only draft pages may be edited.',
        );
    }

    private function normaliseInput(): void
    {
        $sanitizer = app(
            ContentSanitizer::class,
        );

        $this->title = trim(
            $this->title,
        );

        $this->slug = Str::slug(
            trim($this->slug),
        );

        $this->excerpt = $sanitizer->plainText(
            $this->excerpt,
            500,
        );

        $this->content = $sanitizer->sanitize(
            $this->content,
        );

        $this->seoTitle = $sanitizer->plainText(
            $this->seoTitle,
            70,
        );

        $this->metaDescription =
            $sanitizer->plainText(
                $this->metaDescription,
                160,
            );

        $this->canonicalUrl = trim(
            $this->canonicalUrl,
        );

        $this->ogTitle = $sanitizer->plainText(
            $this->ogTitle,
            95,
        );

        $this->ogDescription =
            $sanitizer->plainText(
                $this->ogDescription,
                200,
            );

        $this->ogImage = trim(
            $this->ogImage,
        );
    }

    private function stringValue(
        mixed $value,
    ): string {
        return is_string($value)
            ? $value
            : '';
    }
}
