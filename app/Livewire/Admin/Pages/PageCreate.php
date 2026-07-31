<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;
use App\Support\PageSlugger;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

final class PageCreate extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public bool $slugManuallyEdited = false;

    public function mount(): void
    {
        Gate::authorize('pages.create');
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

        $this->resetValidation('slug');
    }

    public function save(): void
    {
        Gate::authorize('pages.create');

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
                    'blocks' => null,
                    'status' => PageStatus::Draft->value,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                    'approved_by' => null,
                    'submitted_at' => null,
                    'approved_at' => null,
                    'published_at' => null,
                    'archived_at' => null,
                ]);

                app(AuditLogger::class)->log(
                    event: 'pages.created',
                    description: 'Website page created as a draft.',
                    actor: $actor,
                    subject: $page,
                    newValues: [
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'status' => PageStatus::Draft->value,
                        'excerpt_present' => $page->excerpt !== null,
                        'content_present' => $page->content !== null,
                    ],
                );

                return $page;
            },
        );

        session()->flash(
            'status',
            "{$page->title} was created as a draft.",
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

            /*
             * Plain text content for the foundation stage.
             * Rich HTML sanitisation will be added with the editor.
             */
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

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
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
