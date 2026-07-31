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

    public bool $slugManuallyEdited = true;

    public function mount(Page $page): void
    {
        Gate::authorize('pages.update');

        $this->ensureDraftPage($page);

        $this->pageId = $page->id;
        $this->title = (string) $page->title;
        $this->slug = (string) $page->slug;

        $this->excerpt = is_string($page->excerpt)
            ? $page->excerpt
            : '';

        $this->content = is_string($page->content)
            ? $page->content
            : '';

        /*
         * Existing page slugs should not change automatically
         * when the title is edited.
         */
        $this->slugManuallyEdited = true;
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

        $this->resetValidation('slug');
    }

    public function save(): void
    {
        Gate::authorize('pages.update');

        $actor = $this->actor();
        $page = $this->page();

        $this->ensureDraftPage($page);
        $this->normaliseInput();
        $this->validate();

        $slugSource = $this->slug !== ''
            ? $this->slug
            : $this->title;

        $uniqueSlug = PageSlugger::unique(
            $slugSource,
            $page->id,
        );

        $oldTitle = (string) $page->title;
        $oldSlug = (string) $page->slug;

        $oldExcerpt = is_string($page->excerpt)
            ? $page->excerpt
            : '';

        $oldContent = is_string($page->content)
            ? $page->content
            : '';

        $titleChanged = $oldTitle !== $this->title;
        $slugChanged = $oldSlug !== $uniqueSlug;
        $excerptChanged = $oldExcerpt !== $this->excerpt;
        $contentChanged = $oldContent !== $this->content;

        if (
            ! $titleChanged
            && ! $slugChanged
            && ! $excerptChanged
            && ! $contentChanged
        ) {
            session()->flash(
                'status',
                'No page changes were detected.',
            );

            return;
        }

        DB::transaction(function () use (
            $actor,
            $page,
            $uniqueSlug,
            $oldTitle,
            $oldSlug,
            $oldExcerpt,
            $oldContent,
            $titleChanged,
            $slugChanged,
            $excerptChanged,
            $contentChanged,
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
                $oldValues['title'] = $oldTitle;
                $newValues['title'] = $this->title;
            }

            if ($slugChanged) {
                $oldValues['slug'] = $oldSlug;
                $newValues['slug'] = $uniqueSlug;
            }

            /*
             * Do not write excerpt or page body content into
             * audit logs. Store only safe change metadata.
             */
            if ($excerptChanged) {
                $oldValues['excerpt_length'] = mb_strlen($oldExcerpt);
                $newValues['excerpt_length'] = mb_strlen($this->excerpt);
                $newValues['excerpt_changed'] = true;
            }

            if ($contentChanged) {
                $oldValues['content_length'] = mb_strlen($oldContent);
                $newValues['content_length'] = mb_strlen($this->content);
                $newValues['content_changed'] = true;
            }

            app(AuditLogger::class)->log(
                event: 'pages.updated',
                description: 'Draft website page updated.',
                actor: $actor,
                subject: $page,
                oldValues: $oldValues,
                newValues: $newValues,
            );

            app(PageRevisionService::class)->capture(
                page: $page,
                actor: $actor,
                summary: 'Draft page content updated.',
            );
        });

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

                /*
                 * PageSlugger also guarantees uniqueness.
                 * This rule provides immediate form feedback.
                 */
                Rule::unique('pages', 'slug')
                    ->ignore($this->pageId),
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
        return Page::query()->findOrFail(
            $this->pageId,
        );
    }

    private function ensureDraftPage(Page $page): void
    {
        $status = $page->getRawOriginal('status');

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
    }
}
