<?php

namespace App\Livewire\Admin\News;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsArticleService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class NewsEdit extends Component
{
    #[Locked]
    public int $newsId;

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

    public function mount(
        News $news,
    ): void {
        Gate::authorize(
            'news.update',
        );

        if ($news->trashed()) {
            abort(
                404,
            );
        }

        $this->newsId =
            (int) $news->getKey();

        $this->loadNews(
            $news,
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'news.update',
        );

        $this->normaliseInput();

        $this->validate();

        $news =
            $this->news();

        $category = NewsCategory::query()
            ->findOrFail(
                (int) $this->categoryId,
            );

        $updated = app(
            NewsArticleService::class,
        )->update(
            news: $news,

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

            featuredImage: $this->featuredImage(),

            isFeatured: $this->isFeatured,

            publishedAt: $this->publicationDate(),

            seoTitle: $this->nullable(
                $this->seoTitle,
            ),

            seoDescription: $this->nullable(
                $this->seoDescription,
            ),
        );

        session()->flash(
            'status',
            'News draft was updated successfully.',
        );

        $this->redirectRoute(
            'admin.news.edit',
            [
                'news' => $updated->id,
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
            'news.view',
        );

        $news =
            $this->news();

        $status =
            $news->getAttribute(
                'status',
            );

        $categories = NewsCategory::query()
            ->active()
            ->ordered()
            ->get();

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
            'livewire.admin.news.news-edit',
            [
                'news' => $news,

                'categories' => $categories,

                'images' => $images,

                'status' => $status instanceof NewsStatus
                    ? $status
                    : NewsStatus::Draft,

                'editable' => $status instanceof NewsStatus
                    && $status->isEditable(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit News',
            ],
        );
    }

    private function loadNews(
        News $news,
    ): void {
        $this->title = (string) (
            $news->getAttribute(
                'title',
            )
            ?? ''
        );

        $this->slug = (string) (
            $news->getAttribute(
                'slug',
            )
            ?? ''
        );

        $this->summary = (string) (
            $news->getAttribute(
                'summary',
            )
            ?? ''
        );

        $this->content = (string) (
            $news->getAttribute(
                'content',
            )
            ?? ''
        );

        $categoryId =
            $news->getAttribute(
                'category_id',
            );

        $this->categoryId =
            is_numeric($categoryId)
                ? (string) $categoryId
                : '';

        $featuredImageId =
            $news->getAttribute(
                'featured_image_id',
            );

        $this->featuredImageId =
            is_numeric($featuredImageId)
                ? (string) $featuredImageId
                : '';

        $this->isFeatured =
            (bool) $news->getAttribute(
                'is_featured',
            );

        $publishedAt =
            $news->getAttribute(
                'published_at',
            );

        $this->publishedAt =
            $publishedAt instanceof DateTimeInterface
                ? $publishedAt->format(
                    'Y-m-d\TH:i',
                )
                : '';

        $this->seoTitle = (string) (
            $news->getAttribute(
                'seo_title',
            )
            ?? ''
        );

        $this->seoDescription = (string) (
            $news->getAttribute(
                'seo_description',
            )
            ?? ''
        );
    }

    private function news(): News
    {
        return News::query()
            ->findOrFail(
                $this->newsId,
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
