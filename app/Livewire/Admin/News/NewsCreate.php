<?php

namespace App\Livewire\Admin\News;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsArticleService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class NewsCreate extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $categoryId = '';

    public string $featuredImageId = '';

    public bool $isFeatured = false;

    public string $publishedAt = '';

    public string $seoTitle = '';

    public string $seoDescription = '';

    public function mount(): void
    {
        Gate::authorize(
            'news.create',
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'news.create',
        );

        $this->normaliseInput();

        $this->validate();

        $category = NewsCategory::query()
            ->findOrFail(
                (int) $this->categoryId,
            );

        $featuredImage =
            $this->featuredImage();

        $news = app(
            NewsArticleService::class,
        )->create(
            actor: $this->actor(),

            category: $category,

            title: $this->title,

            content: $this->content,

            summary: $this->nullable(
                $this->summary,
            ),

            slug: $this->nullable(
                $this->slug,
            ),

            featuredImage: $featuredImage,

            isFeatured: $this->isFeatured,

            publishedAt: $this->publicationDate(),

            seoTitle: $this->nullable(
                $this->seoTitle,
            ),

            seoDescription: $this->nullable(
                $this->seoDescription,
            ),
        );

        /*
         * Flash message for the edit page.
         */
        session()->flash(
            'status',
            'News article was created as a draft.',
        );

        /*
         * Livewire redirect.
         *
         * Do not use:
         * return redirect()->route(...)
         *
         * This action returns void.
         */
        $this->redirectRoute(
            'admin.news.edit',
            [
                'news' => $news->id,
            ],
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
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'summary' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'content' => [
                'required',
                'string',
                'max:250000',
            ],

            'categoryId' => [
                'required',
                'integer',
                'exists:news_categories,id',
            ],

            'featuredImageId' => [
                'nullable',
                'integer',
                'exists:media_assets,id',
            ],

            'isFeatured' => [
                'boolean',
            ],

            'publishedAt' => [
                'nullable',
                'date',
            ],

            'seoTitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seoDescription' => [
                'nullable',
                'string',
                'max:320',
            ],
        ];
    }

    public function render(): View
    {
        Gate::authorize(
            'news.create',
        );

        $categories = NewsCategory::query()
            ->active()
            ->ordered()
            ->get();

        /*
         * Only Public image assets can be selected
         * as the main news image.
         */
        $images = MediaAsset::query()
            ->where(
                'type',
                MediaType::Image->value,
            )
            ->where(
                'visibility',
                MediaVisibility::Public->value,
            )
            ->orderByDesc(
                'created_at',
            )
            ->limit(
                100,
            )
            ->get();

        return view(
            'livewire.admin.news.news-create',
            [
                'categories' => $categories,

                'images' => $images,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Create News',
            ],
        );
    }

    private function normaliseInput(): void
    {
        $this->title = trim(
            $this->title,
        );

        $this->slug = trim(
            $this->slug,
        );

        $this->summary = trim(
            $this->summary,
        );

        $this->content = trim(
            $this->content,
        );

        $this->categoryId = trim(
            $this->categoryId,
        );

        $this->featuredImageId = trim(
            $this->featuredImageId,
        );

        $this->publishedAt = trim(
            $this->publishedAt,
        );

        $this->seoTitle = trim(
            $this->seoTitle,
        );

        $this->seoDescription = trim(
            $this->seoDescription,
        );
    }

    private function featuredImage(): ?MediaAsset
    {
        if ($this->featuredImageId === '') {
            return null;
        }

        return MediaAsset::query()
            ->findOrFail(
                (int) $this->featuredImageId,
            );
    }

    private function publicationDate(): ?DateTimeInterface
    {
        if ($this->publishedAt === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->publishedAt,
        );
    }

    private function nullable(
        string $value,
    ): ?string {
        $value = trim(
            $value,
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function actor(): User
    {
        $actor =
            Auth::user();

        if (! $actor instanceof User) {
            throw ValidationException::withMessages([
                'authorization' => 'An authenticated administrator is required.',
            ]);
        }

        return $actor;
    }
}
