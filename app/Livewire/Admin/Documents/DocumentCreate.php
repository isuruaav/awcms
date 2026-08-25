<?php

namespace App\Livewire\Admin\Documents;

use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\DocumentService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

final class DocumentCreate extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $categoryId = '';

    public string $documentDate = '';

    public string $description = '';

    public string $publishedAt = '';

    public string $seoTitle = '';

    public string $seoDescription = '';

    public function mount(): void
    {
        Gate::authorize(
            'documents.create',
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'documents.create',
        );

        $this->normaliseInput();

        $this->validate();

        $document =
            app(
                DocumentService::class,
            )->create(
                actor: $this->actor(),

                title: $this->title,

                category: $this->category(),

                description: $this->nullableString(
                    $this->description,
                ),

                documentDate: $this->documentDate(),

                slug: $this->nullableString(
                    $this->slug,
                ),

                publishedAt: $this->publicationDate(),

                seoTitle: $this->nullableString(
                    $this->seoTitle,
                ),

                seoDescription: $this->nullableString(
                    $this->seoDescription,
                ),
            );

        session()->flash(
            'status',
            'Document created successfully. You can now upload the first PDF version.',
        );

        $this->redirectRoute(
            'admin.documents.edit',
            [
                'document' => $document->getKey(),
            ],
            navigate: true,
        );
    }

    public function render(): View
    {
        Gate::authorize(
            'documents.create',
        );

        return view(
            'livewire.admin.documents.document-create',
            [
                'categories' => DocumentCategory::query()
                    ->active()
                    ->ordered()
                    ->get([
                        'id',
                        'name',
                    ]),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Create Document',
            ],
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

            'categoryId' => [
                'nullable',
                'integer',
                'exists:document_categories,id',
            ],

            'documentDate' => [
                'nullable',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
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

        $this->categoryId =
            trim(
                $this->categoryId,
            );

        $this->documentDate =
            trim(
                $this->documentDate,
            );

        $this->description =
            trim(
                $this->description,
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
    }

    private function category(): ?DocumentCategory
    {
        if ($this->categoryId === '') {
            return null;
        }

        return DocumentCategory::query()
            ->findOrFail(
                (int) $this->categoryId,
            );
    }

    private function documentDate(): ?CarbonImmutable
    {
        if ($this->documentDate === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->documentDate,
        )->startOfDay();
    }

    private function publicationDate(): ?CarbonImmutable
    {
        if ($this->publishedAt === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->publishedAt,
        );
    }

    private function nullableString(
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

    private function actor(): User
    {
        $actor =
            Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }
}
