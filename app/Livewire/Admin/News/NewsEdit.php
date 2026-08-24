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
use App\Services\NewsWorkflowService;
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

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    public string $changeRequestNote = '';

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Save Article
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        Gate::authorize(
            'news.update',
        );

        $news =
            $this->news();

        $status =
            $this->status(
                $news,
            );

        if (! $status->isEditable()) {
            throw ValidationException::withMessages([
                'workflow' => 'This news article is currently locked for editing.',
            ]);
        }

        $updated =
            $this->persistArticle(
                $news,
            );

        session()->flash(
            'status',
            'News article was updated successfully.',
        );

        $this->redirectToArticle(
            $updated,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit for Review
    |--------------------------------------------------------------------------
    */

    public function submitForReview(): void
    {
        Gate::authorize(
            'news.submit',
        );

        $news =
            $this->news();

        $status =
            $this->status(
                $news,
            );

        if (
            ! in_array(
                $status,
                [
                    NewsStatus::Draft,
                    NewsStatus::ChangesRequested,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'workflow' => 'Only Draft or Changes Requested news can be submitted for review.',
            ]);
        }

        /*
         * Save the current form first.
         *
         * This ensures the version submitted for review is the
         * version currently visible in the editor.
         */
        $news =
            $this->persistArticle(
                $news,
            );

        $news =
            app(
                NewsWorkflowService::class,
            )->submit(
                news: $news,
                actor: $this->actor(),
            );

        session()->flash(
            'status',
            'News article was submitted for review successfully.',
        );

        $this->redirectToArticle(
            $news,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Request Changes
    |--------------------------------------------------------------------------
    */

    public function requestChanges(): void
    {
        Gate::authorize(
            'news.request-changes',
        );

        $this->changeRequestNote =
            trim(
                $this->changeRequestNote,
            );

        $this->validate(
            [
                'changeRequestNote' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'changeRequestNote.required' => 'Please explain the changes required.',

                'changeRequestNote.max' => 'The change request note may not exceed 1000 characters.',
            ],
        );

        $news =
            app(
                NewsWorkflowService::class,
            )->requestChanges(
                news: $this->news(),
                actor: $this->actor(),
                note: $this->changeRequestNote,
            );

        $this->changeRequestNote = '';

        session()->flash(
            'status',
            'Changes were requested successfully.',
        );

        $this->redirectToArticle(
            $news,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(): void
    {
        Gate::authorize(
            'news.approve',
        );

        $news =
            app(
                NewsWorkflowService::class,
            )->approve(
                news: $this->news(),
                actor: $this->actor(),
            );

        session()->flash(
            'status',
            'News article was approved successfully.',
        );

        $this->redirectToArticle(
            $news,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    public function publish(): void
    {
        Gate::authorize(
            'news.publish',
        );

        $news =
            app(
                NewsWorkflowService::class,
            )->publish(
                news: $this->news(),
                actor: $this->actor(),
            );

        session()->flash(
            'status',
            'News article was published successfully.',
        );

        $this->redirectToArticle(
            $news,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Archive
    |--------------------------------------------------------------------------
    */

    public function archive(): void
    {
        Gate::authorize(
            'news.archive',
        );

        $news =
            app(
                NewsWorkflowService::class,
            )->archive(
                news: $this->news(),
                actor: $this->actor(),
            );

        session()->flash(
            'status',
            'News article was archived successfully.',
        );

        $this->redirectToArticle(
            $news,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        Gate::authorize(
            'news.view',
        );

        $news =
            $this->news();

        $status =
            $this->status(
                $news,
            );

        $categories =
            NewsCategory::query()
                ->active()
                ->ordered()
                ->get();

        /*
         * If an old/current category was later disabled,
         * keep it visible in the form for context.
         */
        $currentCategory =
            $news->category;

        if (
            $currentCategory instanceof NewsCategory
            && ! $categories->contains(
                'id',
                $currentCategory->id,
            )
        ) {
            $categories->prepend(
                $currentCategory,
            );
        }

        $images =
            MediaAsset::query()
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

                'status' => $status,

                'editable' => $status->isEditable()
                    && Gate::allows(
                        'news.update',
                    ),

                /*
                 * Workflow action visibility.
                 */
                'canSubmit' => Gate::allows(
                    'news.submit',
                )
                    && in_array(
                        $status,
                        [
                            NewsStatus::Draft,
                            NewsStatus::ChangesRequested,
                        ],
                        true,
                    ),

                'canRequestChanges' => Gate::allows(
                    'news.request-changes',
                )
                    && $status ===
                        NewsStatus::Submitted,

                'canApprove' => Gate::allows(
                    'news.approve',
                )
                    && $status ===
                        NewsStatus::Submitted,

                'canPublish' => Gate::allows(
                    'news.publish',
                )
                    && $status ===
                        NewsStatus::Approved,

                'canArchive' => Gate::allows(
                    'news.archive',
                )
                    && $status ===
                        NewsStatus::Published,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit News',
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Persist Editor Data
    |--------------------------------------------------------------------------
    */

    private function persistArticle(
        News $news,
    ): News {
        Gate::authorize(
            'news.update',
        );

        $status =
            $this->status(
                $news,
            );

        if (! $status->isEditable()) {
            throw ValidationException::withMessages([
                'workflow' => 'This workflow state cannot be edited.',
            ]);
        }

        $this->normaliseInput();

        $this->validate(
            $this->rules(),
        );

        $category =
            NewsCategory::query()
                ->findOrFail(
                    (int) $this->categoryId,
                );

        return app(
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
    }

    /*
    |--------------------------------------------------------------------------
    | Load Article Into Editor
    |--------------------------------------------------------------------------
    */

    private function loadNews(
        News $news,
    ): void {
        $this->title =
            (string) (
                $news->getAttribute(
                    'title',
                )
                ?? ''
            );

        $this->slug =
            (string) (
                $news->getAttribute(
                    'slug',
                )
                ?? ''
            );

        $this->summary =
            (string) (
                $news->getAttribute(
                    'summary',
                )
                ?? ''
            );

        $this->content =
            (string) (
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
            is_numeric(
                $categoryId,
            )
                ? (string) $categoryId
                : '';

        $featuredImageId =
            $news->getAttribute(
                'featured_image_id',
            );

        $this->featuredImageId =
            is_numeric(
                $featuredImageId,
            )
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

        $this->seoTitle =
            (string) (
                $news->getAttribute(
                    'seo_title',
                )
                ?? ''
            );

        $this->seoDescription =
            (string) (
                $news->getAttribute(
                    'seo_description',
                )
                ?? ''
            );

        $changeRequestNote =
            $news->getAttribute(
                'change_request_note',
            );

        $this->changeRequestNote =
            is_string(
                $changeRequestNote,
            )
                ? $changeRequestNote
                : '';
    }

    /*
    |--------------------------------------------------------------------------
    | Model Helpers
    |--------------------------------------------------------------------------
    */

    private function news(): News
    {
        $news =
            News::query()
                ->with([
                    'category',
                ])
                ->findOrFail(
                    $this->newsId,
                );

        if ($news->trashed()) {
            abort(
                404,
            );
        }

        return $news;
    }

    private function status(
        News $news,
    ): NewsStatus {
        $status =
            $news->getAttribute(
                'status',
            );

        if (! $status instanceof NewsStatus) {
            throw ValidationException::withMessages([
                'workflow' => 'The news article has an invalid workflow status.',
            ]);
        }

        return $status;
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

    /*
    |--------------------------------------------------------------------------
    | Input Helpers
    |--------------------------------------------------------------------------
    */

    private function normaliseInput(): void
    {
        $this->title =
            trim(
                $this->title,
            );

        $this->slug =
            trim(
                $this->slug,
            );

        $this->summary =
            trim(
                $this->summary,
            );

        $this->content =
            trim(
                $this->content,
            );

        $this->categoryId =
            trim(
                $this->categoryId,
            );

        $this->featuredImageId =
            trim(
                $this->featuredImageId,
            );

        $this->publishedAt =
            trim(
                $this->publishedAt,
            );

        $this->seoTitle =
            trim(
                $this->seoTitle,
            );

        $this->seoDescription =
            trim(
                $this->seoDescription,
            );

        $this->changeRequestNote =
            trim(
                $this->changeRequestNote,
            );
    }

    private function nullable(
        string $value,
    ): ?string {
        $value =
            trim(
                $value,
            );

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    private function redirectToArticle(
        News $news,
    ): void {
        $this->redirectRoute(
            'admin.news.edit',
            [
                'news' => (int) $news->getKey(),
            ],
            navigate: true,
        );
    }
}
