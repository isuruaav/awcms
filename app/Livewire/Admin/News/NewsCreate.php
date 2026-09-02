<?php

namespace App\Livewire\Admin\News;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Enums\NewsEditorMode;
use App\Enums\NewsLocale;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
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

final class NewsCreate extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $categoryId = '';

    public string $featuredImageId = '';

    public bool $isFeatured = false;

    public string $publishedAt = '';

    public string $locale = NewsLocale::English->value;

    public string $editorMode = NewsEditorMode::Visual->value;

    /** @var array<int, TemporaryUploadedFile> */
    public array $galleryUploads = [];

    #[Locked]
    public string $translationGroup = '';

    #[Locked]
    public ?int $translationSourceNewsId = null;

    #[Locked]
    public string $translationSourceTitle = '';

    /* Kept for backward compatibility; intentionally hidden from the new UI. */
    public string $seoTitle = '';

    public string $seoDescription = '';

    public bool $slugManuallyEdited = false;

    public function mount(
        ?int $newsId = null,
        ?string $locale = null,
    ): void {
        Gate::authorize('news.create');

        if ($newsId === null) {
            $this->translationGroup = Str::uuid()->toString();

            return;
        }

        $news = News::query()->findOrFail($newsId);

        $targetLocale = is_string($locale)
            ? NewsLocale::tryFrom($locale)
            : null;

        abort_unless(
            $targetLocale instanceof NewsLocale,
            404,
        );

        $rawSourceLocale = $news->getRawOriginal('locale');

        $sourceLocale = is_string($rawSourceLocale)
            ? NewsLocale::tryFrom($rawSourceLocale)
            : null;

        $sourceLocale ??= NewsLocale::English;

        abort_if(
            $sourceLocale === $targetLocale,
            409,
            'That language version already exists.',
        );

        $translationGroup = $news->getAttribute('translation_group');

        abort_unless(
            is_string($translationGroup) && trim($translationGroup) !== '',
            409,
            'The source article does not have a translation group.',
        );

        $existing = News::withTrashed()
            ->where('translation_group', $translationGroup)
            ->where('locale', $targetLocale->value)
            ->exists();

        abort_if(
            $existing,
            409,
            'That language version already exists.',
        );

        $this->locale = $targetLocale->value;
        $this->translationGroup = $translationGroup;
        $this->translationSourceNewsId = (int) $news->id;
        $this->translationSourceTitle = (string) $news->title;

        /*
         * No automatic translation and no source body copy.
         * The operator enters the approved language content manually.
         */
        $this->title = '';
        $this->slug = '';
        $this->summary = '';
        $this->content = '';
        $this->editorMode = NewsEditorMode::Visual->value;

        if ($news->category_id !== null) {
            $this->categoryId = (string) $news->category_id;
        }
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

    public function updatedLocale(): void
    {
        if ($this->translationSourceNewsId !== null) {
            return;
        }

        if (NewsLocale::tryFrom($this->locale) === null) {
            $this->locale = NewsLocale::English->value;
        }
    }

    public function regenerateSlug(): void
    {
        $this->slugManuallyEdited = false;
        $this->slug = Str::slug($this->title);
    }

    public function save(): void
    {
        Gate::authorize('news.create');

        $this->normaliseInput();
        $this->validate();

        $locale = NewsLocale::from($this->locale);
        $editorMode = NewsEditorMode::from($this->editorMode);
        $actor = $this->actor();

        $category = NewsCategory::query()->findOrFail(
            (int) $this->categoryId,
        );

        $uploadedGalleryMedia = $this->uploadGalleryMedia(
            $actor,
        );

        $news = app(NewsArticleService::class)->create(
            actor: $actor,
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
            locale: $locale,
            translationGroup: $this->translationGroup,
            editorMode: $editorMode,
        );

        foreach ($uploadedGalleryMedia as $media) {
            app(NewsImageService::class)->addImage(
                news: $news,
                media: $media,
                actor: $actor,
            );
        }

        session()->flash(
            'status',
            'News article was created as a draft.',
        );

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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:250000'],
            'categoryId' => ['required', 'integer', 'exists:news_categories,id'],
            'featuredImageId' => ['nullable', 'integer', 'exists:media_assets,id'],
            'isFeatured' => ['boolean'],
            'publishedAt' => ['nullable', 'date'],
            'locale' => ['required', Rule::enum(NewsLocale::class)],
            'editorMode' => ['required', Rule::enum(NewsEditorMode::class)],
            'galleryUploads' => ['array', 'max:20'],
            'galleryUploads.*' => ['file', 'image', 'max:8192'],
            'seoTitle' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function render(): View
    {
        Gate::authorize('news.create');

        return view(
            'livewire.admin.news.news-create',
            [
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
        $this->title = trim($this->title);
        $this->slug = Str::slug($this->slug);
        $this->summary = trim($this->summary);
        $this->categoryId = trim($this->categoryId);
        $this->featuredImageId = trim($this->featuredImageId);
        $this->publishedAt = trim($this->publishedAt);
        $this->seoTitle = trim($this->seoTitle);
        $this->seoDescription = trim($this->seoDescription);

        if (NewsLocale::tryFrom($this->locale) === null) {
            $this->locale = NewsLocale::English->value;
        }

        if (NewsEditorMode::tryFrom($this->editorMode) === null) {
            $this->editorMode = NewsEditorMode::Visual->value;
        }
    }

    /**
     * @return list<MediaAsset>
     */
    private function uploadGalleryMedia(User $actor): array
    {
        if ($this->galleryUploads === []) {
            return [];
        }

        Gate::forUser($actor)->authorize(
            'media.upload',
        );

        Gate::forUser($actor)->authorize(
            'news.update',
        );

        /** @var list<MediaAsset> $uploaded */
        $uploaded = [];

        foreach ($this->galleryUploads as $file) {
            $originalName = $file->getClientOriginalName();
            $title = pathinfo(
                $originalName,
                PATHINFO_FILENAME,
            );

            $uploaded[] = app(MediaUploadService::class)->upload(
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
        }

        return $uploaded;
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
