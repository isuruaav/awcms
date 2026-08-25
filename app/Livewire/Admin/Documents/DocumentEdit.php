<?php

namespace App\Livewire\Admin\Documents;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\DocumentService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

final class DocumentEdit extends Component
{
    public int $documentId;

    /*
    |--------------------------------------------------------------------------
    | Document Metadata
    |--------------------------------------------------------------------------
    */

    public string $title = '';

    public string $slug = '';

    public string $categoryId = '';

    public string $documentDate = '';

    public string $description = '';

    public string $publishedAt = '';

    public string $seoTitle = '';

    public string $seoDescription = '';

    /*
    |--------------------------------------------------------------------------
    | New PDF Version
    |--------------------------------------------------------------------------
    */

    public string $selectedMediaId = '';

    public string $versionLabel = '';

    public string $changeNote = '';

    public function mount(
        Document $document,
    ): void {
        Gate::authorize(
            'documents.view',
        );

        Gate::authorize(
            'documents.update',
        );

        abort_if(
            $document->trashed(),
            404,
        );

        $this->loadDocument(
            $document,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save Metadata
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        Gate::authorize(
            'documents.update',
        );

        $this->normaliseInput();

        $this->validate();

        $document =
            app(
                DocumentService::class,
            )->update(
                document: $this->document(),

                actor: $this->actor(),

                title: $this->title,

                category: $this->category(),

                description: $this->nullableString(
                    $this->description,
                ),

                documentDate: $this->documentDateValue(),

                slug: $this->nullableString(
                    $this->slug,
                ),

                publishedAt: $this->publicationDateValue(),

                seoTitle: $this->nullableString(
                    $this->seoTitle,
                ),

                seoDescription: $this->nullableString(
                    $this->seoDescription,
                ),
            );

        $this->loadDocument(
            $document,
        );

        session()->flash(
            'status',
            'Document updated successfully.',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Add PDF Version
    |--------------------------------------------------------------------------
    */

    public function addVersion(): void
    {
        Gate::authorize(
            'documents.update',
        );

        $this->selectedMediaId =
            trim(
                $this->selectedMediaId,
            );

        $this->versionLabel =
            trim(
                $this->versionLabel,
            );

        $this->changeNote =
            trim(
                $this->changeNote,
            );

        $this->validate([
            'selectedMediaId' => [
                'required',
                'integer',
                'exists:media_assets,id',
            ],

            'versionLabel' => [
                'nullable',
                'string',
                'max:100',
            ],

            'changeNote' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $media =
            MediaAsset::withTrashed()
                ->findOrFail(
                    (int) $this->selectedMediaId,
                );

        app(
            DocumentService::class,
        )->addVersion(
            document: $this->document(),

            actor: $this->actor(),

            media: $media,

            versionLabel: $this->nullableString(
                $this->versionLabel,
            ),

            changeNote: $this->nullableString(
                $this->changeNote,
            ),
        );

        $this->selectedMediaId = '';

        $this->versionLabel = '';

        $this->changeNote = '';

        $this->loadDocument(
            $this->document(),
        );

        session()->flash(
            'status',
            'New PDF version added successfully.',
        );
    }

    /*
|--------------------------------------------------------------------------
| Restore PDF Version
|--------------------------------------------------------------------------
*/

    public function restoreVersion(
        int $versionId,
    ): void {
        Gate::authorize(
            'documents.update',
        );

        $version =
            DocumentVersion::query()
                ->findOrFail(
                    $versionId,
                );

        $document =
            app(
                DocumentService::class,
            )->restoreVersion(
                document: $this->document(),

                version: $version,

                actor: $this->actor(),
            );

        $this->loadDocument(
            $document,
        );

        session()->flash(
            'status',
            sprintf(
                'PDF version %d restored successfully.',
                $version->version,
            ),
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
            'documents.publish',
        );

        $document =
            app(
                DocumentService::class,
            )->publish(
                document: $this->document(),

                actor: $this->actor(),
            );

        $this->loadDocument(
            $document,
        );

        session()->flash(
            'status',
            'Document published successfully.',
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
            'documents.archive',
        );

        $document =
            app(
                DocumentService::class,
            )->archive(
                document: $this->document(),

                actor: $this->actor(),
            );

        $this->loadDocument(
            $document,
        );

        session()->flash(
            'status',
            'Document archived successfully.',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        Gate::authorize(
            'documents.view',
        );

        Gate::authorize(
            'documents.update',
        );

        $document =
            $this->document()
                ->load([
                    'category',
                    'creator',
                    'updater',
                    'publisher',
                    'archiver',
                    'versions.media',
                    'versions.uploader',
                ]);

        $editable =
            $document->isEditable();

        $categories =
            DocumentCategory::query()
                ->active()
                ->ordered()
                ->get([
                    'id',
                    'name',
                ]);

        /*
         * Only valid public uploaded PDF media should appear
         * in the version selector.
         *
         * Files already used by this document are excluded.
         */
        $mediaAssets =
            MediaAsset::query()
                ->where(
                    'type',
                    MediaType::Document->value,
                )
                ->where(
                    'visibility',
                    MediaVisibility::Public->value,
                )
                ->where(
                    'source',
                    '!=',
                    MediaSource::External->value,
                )
                ->where(
                    'mime_type',
                    'application/pdf',
                )
                ->where(
                    'extension',
                    'pdf',
                )
                ->whereNotNull(
                    'disk',
                )
                ->whereNotNull(
                    'path',
                )
                ->whereNotIn(
                    'id',
                    DocumentVersion::query()
                        ->select(
                            'media_asset_id',
                        )
                        ->where(
                            'document_id',
                            $this->documentId,
                        ),
                )
                ->orderByDesc(
                    'id',
                )
                ->limit(
                    150,
                )
                ->get();

        $currentVersion =
            $document->versions
                ->first(
                    static fn (
                        DocumentVersion $version,
                    ): bool => $version->version === $document->current_version,
                );

        return view(
            'livewire.admin.documents.document-edit',
            [
                'document' => $document,

                'editable' => $editable,

                'categories' => $categories,

                'mediaAssets' => $mediaAssets,

                'currentVersion' => $currentVersion,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit Document',
            ],
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

    /*
    |--------------------------------------------------------------------------
    | Document Loading
    |--------------------------------------------------------------------------
    */

    private function loadDocument(
        Document $document,
    ): void {
        $this->documentId =
            (int) $document->getKey();

        $this->title =
            $this->stringValue(
                $document->getAttribute(
                    'title',
                ),
            );

        $this->slug =
            $this->stringValue(
                $document->getAttribute(
                    'slug',
                ),
            );

        $categoryId =
            $document->getAttribute(
                'document_category_id',
            );

        $this->categoryId =
            is_numeric(
                $categoryId,
            )
            ? (string) ((int) $categoryId)
            : '';

        $documentDate =
            $document->getAttribute(
                'document_date',
            );

        $this->documentDate =
            $documentDate instanceof DateTimeInterface
            ? $documentDate->format(
                'Y-m-d',
            )
            : '';

        $this->description =
            $this->stringValue(
                $document->getAttribute(
                    'description',
                ),
            );

        $publishedAt =
            $document->getAttribute(
                'published_at',
            );

        $this->publishedAt =
            $publishedAt instanceof DateTimeInterface
            ? $publishedAt->format(
                'Y-m-d\TH:i',
            )
            : '';

        $this->seoTitle =
            $this->stringValue(
                $document->getAttribute(
                    'seo_title',
                ),
            );

        $this->seoDescription =
            $this->stringValue(
                $document->getAttribute(
                    'seo_description',
                ),
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Helpers
    |--------------------------------------------------------------------------
    */

    private function document(): Document
    {
        return Document::query()
            ->findOrFail(
                $this->documentId,
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

    /*
    |--------------------------------------------------------------------------
    | Date Helpers
    |--------------------------------------------------------------------------
    */

    private function documentDateValue(): ?CarbonImmutable
    {
        if ($this->documentDate === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->documentDate,
        )->startOfDay();
    }

    private function publicationDateValue(): ?CarbonImmutable
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

    private function stringValue(
        mixed $value,
    ): string {
        return is_string(
            $value,
        )
            ? $value
            : '';
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
