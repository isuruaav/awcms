<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;
use App\Services\PageBlockSanitizer;
use App\Services\PageRevisionService;
use App\Support\PageBlockFactory;
use App\Support\PageSlugger;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class PageCreate extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    /**
     * @var list<array<string, mixed>>
     */
    public array $blocks = [];

    public string $seoTitle = '';

    public string $metaDescription = '';

    public string $canonicalUrl = '';

    public bool $robotsIndex = true;

    public string $ogTitle = '';

    public string $ogDescription = '';

    public string $ogImage = '';

    public bool $slugManuallyEdited = false;

    public function mount(): void
    {
        Gate::authorize(
            'pages.create',
        );
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

    public function addBlock(
        string $type,
    ): void {
        Gate::authorize(
            'pages.create',
        );

        if (
            count($this->blocks)
            >= PageBlockSanitizer::MAX_BLOCKS
        ) {
            throw ValidationException::withMessages([
                'blocks' => sprintf(
                    'A page may contain a maximum of %d blocks.',
                    PageBlockSanitizer::MAX_BLOCKS,
                ),
            ]);
        }

        $this->blocks[] = PageBlockFactory::make(
            $type,
        );

        $this->resetValidation(
            'blocks',
        );
    }

    public function removeBlock(
        int $index,
    ): void {
        Gate::authorize(
            'pages.create',
        );

        if (
            $index < 0
            || $index >= count($this->blocks)
        ) {
            return;
        }

        array_splice(
            $this->blocks,
            $index,
            1,
        );

        $this->resetValidation(
            'blocks',
        );
    }

    public function moveBlockUp(
        int $index,
    ): void {
        Gate::authorize(
            'pages.create',
        );

        if (
            $index <= 0
            || ! array_key_exists(
                $index,
                $this->blocks,
            )
        ) {
            return;
        }

        $this->swapBlocks(
            $index,
            $index - 1,
        );
    }

    public function moveBlockDown(
        int $index,
    ): void {
        Gate::authorize(
            'pages.create',
        );

        if (
            $index < 0
            || $index >= count($this->blocks) - 1
        ) {
            return;
        }

        $this->swapBlocks(
            $index,
            $index + 1,
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'pages.create',
        );

        $actor = $this->actor();

        $this->normaliseInput();
        $this->validate();

        $slugSource = $this->slug !== ''
            ? $this->slug
            : $this->title;

        $uniqueSlug = PageSlugger::unique(
            $slugSource,
        );

        $page = DB::transaction(
            function () use (
                $actor,
                $uniqueSlug,
            ): Page {
                $page = Page::query()->create([
                    'title' => $this->title,

                    'slug' => $uniqueSlug,

                    'excerpt' => $this->excerpt !== ''
                        ? $this->excerpt
                        : null,

                    'content' => $this->content !== ''
                        ? $this->content
                        : null,

                    'blocks' => $this->blocks !== []
                        ? $this->blocks
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

                    /*
                     * Workflow
                     */
                    'status' => PageStatus::Draft->value,

                    'created_by' => $actor->id,

                    'updated_by' => $actor->id,
                ]);

                app(AuditLogger::class)->log(
                    event: 'pages.created',
                    description: 'Draft website page created.',
                    actor: $actor,
                    subject: $page,
                    oldValues: [],
                    newValues: [
                        'title' => $page->title,

                        'slug' => $page->slug,

                        'status' => PageStatus::Draft->value,

                        'excerpt_present' => $page->excerpt !== null,

                        'content_present' => $page->content !== null,

                        'blocks_count' => count($this->blocks),

                        'seo_title_present' => $page->seo_title !== null,

                        'meta_description_present' => $page->meta_description
                            !== null,

                        'canonical_url_present' => $page->canonical_url !== null,

                        'robots_index' => (bool) $page->robots_index,

                        'og_title_present' => $page->og_title !== null,

                        'og_description_present' => $page->og_description !== null,

                        'og_image_present' => $page->og_image !== null,
                    ],
                );

                app(PageRevisionService::class)
                    ->capture(
                        page: $page,
                        actor: $actor,
                        summary: 'Initial draft created.',
                    );

                return $page;
            },
        );

        session()->flash(
            'status',
            "{$page->title} was created successfully.",
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

            'blocks' => [
                'array',
                'max:100',
            ],

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

            'canonicalUrl.url' => 'The canonical URL must be a valid HTTP or HTTPS URL.',

            'ogImage.url' => 'The Open Graph image must be a valid HTTP or HTTPS URL.',
        ];
    }

    public function render(): View
    {
        return view(
            'livewire.admin.pages.page-create',
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Create Page',
            ],
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

        $this->blocks = app(
            PageBlockSanitizer::class,
        )->normalize(
            $this->blocks,
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

    private function swapBlocks(
        int $firstIndex,
        int $secondIndex,
    ): void {
        $temporary =
            $this->blocks[$firstIndex];

        $this->blocks[$firstIndex] =
            $this->blocks[$secondIndex];

        $this->blocks[$secondIndex] =
            $temporary;
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
}
