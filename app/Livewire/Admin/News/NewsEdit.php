<?php

namespace App\Livewire\Admin\News;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Enums\NewsEditorMode;
use App\Enums\NewsLocale;
use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsImage;
use App\Models\User;
use App\Services\MediaUploadService;
use App\Services\NewsArticleService;
use App\Services\NewsImageService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class NewsEdit extends Component
{
    use WithFileUploads;

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

    public string $editorMode = NewsEditorMode::Visual->value;

    /** @var array<int, TemporaryUploadedFile> */
    public array $galleryUploads = [];

    /* Kept for backward compatibility; intentionally hidden from the new UI. */
    public string $seoTitle = '';

    public string $seoDescription = '';

    public bool $slugManuallyEdited = true;

    public function mount(News $news): void
    {
        Gate::authorize('news.update');

        abort_if(
            $news->trashed(),
            404,
        );

        $this->newsId = (int) $news->id;
        $this->loadNews($news);
    }

    public function updatedTitle(): void
    {
        if ($this->slugManuallyEdited) {
            return;
        }

        $this->slug = Str::slug($this->title);
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
        $this->slug = Str::slug($this->slug);
    }

    public function regenerateSlug(): void
    {
        $this->slugManuallyEdited = false;
        $this->slug = Str::slug($this->title);
    }

    public function save(): void
    {
        Gate::authorize('news.update');

        $news = $this->news();

        abort_if(
            $this->statusOf($news) !== NewsStatus::Draft,
            409,
            'Unpublish this article before editing it.',
        );

        $this->normaliseInput();
        $this->validate();

        $category = NewsCategory::query()->findOrFail(
            (int) $this->categoryId,
        );

        $news = app(NewsArticleService::class)->update(
            news: $news,
            actor: $this->actor(),
            category: $category,
            title: $this->title,
            content: $this->content,
            summary: $this->nullable($this->summary),
            slug: $this->nullable($this->slug),
            featuredImage: $this->featuredImage(),
            isFeatured: $this->isFeatured,
            publishedAt: $this->publicationDate(),
            seoTitle: $this->nullable($this->seoTitle),
            seoDescription: $this->nullable($this->seoDescription),
            editorMode: NewsEditorMode::from($this->editorMode),
        );

        $this->loadNews($news);

        session()->flash(
            'status',
            'News article saved.',
        );
    }

    public function uploadGalleryImages(): void
    {
        Gate::authorize('news.update');
        Gate::authorize('media.upload');

        $news = $this->news();

        abort_if(
            $this->statusOf($news) !== NewsStatus::Draft,
            409,
            'Unpublish this article before changing its images.',
        );

        $this->validate([
            'galleryUploads' => ['required', 'array', 'min:1', 'max:20'],
            'galleryUploads.*' => ['file', 'image', 'max:8192'],
        ]);

        $actor = $this->actor();

        foreach ($this->galleryUploads as $file) {
            $originalName = $file->getClientOriginalName();
            $title = pathinfo(
                $originalName,
                PATHINFO_FILENAME,
            );

            $media = app(MediaUploadService::class)->upload(
                file: $file,
                type: MediaType::Image,
                visibility: MediaVisibility::Public,
                actor: $actor,
                title: trim($title) !== ''
                    ? $title
                    : 'News image',
                altText: null,
                caption: null,
            );

            app(NewsImageService::class)->addImage(
                news: $news,
                media: $media,
                actor: $actor,
            );
        }

        $this->reset('galleryUploads');

        session()->flash(
            'status',
            'News images uploaded successfully.',
        );
    }

    public function removeGalleryImage(int $newsImageId): void
    {
        Gate::authorize('news.update');

        $newsImage = NewsImage::query()
            ->where('news_id', $this->newsId)
            ->findOrFail($newsImageId);

        app(NewsImageService::class)->removeImage(
            newsImage: $newsImage,
            actor: $this->actor(),
        );

        session()->flash(
            'status',
            'News image removed from this article.',
        );
    }

    public function selectFeaturedImage(int $newsImageId): void
    {
        Gate::authorize('news.update');

        $newsImage = NewsImage::query()
            ->where('news_id', $this->newsId)
            ->findOrFail($newsImageId);

        app(NewsImageService::class)->setFeaturedImage(
            newsImage: $newsImage,
            actor: $this->actor(),
        );

        $this->featuredImageId = (string) $newsImage->media_asset_id;

        session()->flash(
            'status',
            'Featured image selected.',
        );
    }

    public function clearFeaturedImage(): void
    {
        Gate::authorize('news.update');

        app(NewsImageService::class)->clearFeaturedImage(
            news: $this->news(),
            actor: $this->actor(),
        );

        $this->featuredImageId = '';

        session()->flash(
            'status',
            'Featured image cleared.',
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:250000'],
            'categoryId' => ['required', 'integer', 'exists:news_categories,id'],
            'featuredImageId' => ['nullable', 'integer', 'exists:media_assets,id'],
            'isFeatured' => ['boolean'],
            'publishedAt' => ['nullable', 'date'],
            'editorMode' => ['required', Rule::enum(NewsEditorMode::class)],
            'galleryUploads' => ['array', 'max:20'],
            'galleryUploads.*' => ['file', 'image', 'max:8192'],
            'seoTitle' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function render(): View
    {
        Gate::authorize('news.update');

        $news = $this->news()->load([
            'translationVersions',
            'images.media.variants',
        ]);

        $status = $this->statusOf($news);

        return view(
            'livewire.admin.news.news-edit',
            [
                'news' => $news,
                'categories' => NewsCategory::query()
                    ->active()
                    ->ordered()
                    ->get(),
                'images' => MediaAsset::query()
                    ->where('type', MediaType::Image->value)
                    ->where('visibility', MediaVisibility::Public->value)
                    ->orderByDesc('created_at')
                    ->limit(100)
                    ->get(),
                'locales' => NewsLocale::cases(),
                'status' => $status ?? NewsStatus::Draft,
                'editable' => $status === NewsStatus::Draft,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit News',
            ],
        );
    }

    private function loadNews(News $news): void
    {
        $this->title = $this->stringAttribute(
            $news,
            'title',
        );
        $this->slug = $this->stringAttribute(
            $news,
            'slug',
        );
        $this->summary = $this->stringAttribute(
            $news,
            'summary',
        );
        $this->content = $this->stringAttribute(
            $news,
            'content',
        );

        $categoryId = $news->getAttribute('category_id');
        $this->categoryId = is_numeric($categoryId)
            ? (string) $categoryId
            : '';

        $featuredImageId = $news->getAttribute('featured_image_id');
        $this->featuredImageId = is_numeric($featuredImageId)
            ? (string) $featuredImageId
            : '';

        $this->isFeatured = (bool) $news->getAttribute('is_featured');

        $publishedAt = $news->getAttribute('published_at');
        $this->publishedAt = $publishedAt instanceof DateTimeInterface
            ? $publishedAt->format('Y-m-d\TH:i')
            : '';

        $this->seoTitle = $this->stringAttribute(
            $news,
            'seo_title',
        );
        $this->seoDescription = $this->stringAttribute(
            $news,
            'seo_description',
        );

        $rawEditorMode = $news->getRawOriginal('editor_mode');
        $editorMode = is_string($rawEditorMode)
            ? NewsEditorMode::tryFrom($rawEditorMode)
            : null;

        $this->editorMode = ($editorMode ?? NewsEditorMode::Visual)->value;
    }

    private function normaliseInput(): void
    {
        $this->title = trim($this->title);
        $this->slug = Str::slug($this->slug);
        $this->summary = trim($this->summary);
        $this->categoryId = trim($this->categoryId);
        $this->featuredImageId = trim($this->featuredImageId);
        $this->publishedAt = trim($this->publishedAt);
        $this->seoTitle = trim($this->seoTitle);
        $this->seoDescription = trim($this->seoDescription);

        if (NewsEditorMode::tryFrom($this->editorMode) === null) {
            $this->editorMode = NewsEditorMode::Visual->value;
        }
    }

    private function featuredImage(): ?MediaAsset
    {
        if ($this->featuredImageId === '') {
            return null;
        }

        return MediaAsset::query()->findOrFail(
            (int) $this->featuredImageId,
        );
    }

    private function publicationDate(): ?DateTimeInterface
    {
        if ($this->publishedAt === '') {
            return null;
        }

        return CarbonImmutable::parse($this->publishedAt);
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    private function statusOf(News $news): ?NewsStatus
    {
        $rawStatus = $news->getRawOriginal('status');

        return is_string($rawStatus)
            ? NewsStatus::tryFrom($rawStatus)
            : null;
    }

    private function stringAttribute(News $news, string $attribute): string
    {
        $value = $news->getAttribute($attribute);

        return is_string($value)
            ? $value
            : '';
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

    private function news(): News
    {
        return News::query()->findOrFail(
            $this->newsId,
        );
    }
}
