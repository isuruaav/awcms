<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicDocumentController extends Controller
{
    public function index(): View
    {
        $documents =
            $this->publicDocumentsQuery()
                ->with([
                    'category',
                ])
                ->orderByDesc(
                    'document_date',
                )
                ->orderByDesc(
                    'published_at',
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    15,
                )
                ->withQueryString();

        return view(
            'public.documents.index',
            [
                'documents' => $documents,
            ],
        );
    }

    public function show(
        string $slug,
    ): View {
        $document =
            $this->publicDocument(
                $slug,
            );

        $currentVersion =
            $this->currentVersion(
                $document,
            );

        return view(
            'public.documents.show',
            [
                'document' => $document,

                'currentVersion' => $currentVersion,
            ],
        );
    }

    public function view(
        string $slug,
    ): StreamedResponse {
        $document =
            $this->publicDocument(
                $slug,
            );

        $version =
            $this->currentVersion(
                $document,
            );

        $media =
            $version->media;

        abort_unless(
            $media instanceof MediaAsset,
            404,
        );

        $disk =
            $this->requiredString(
                $media->getAttribute(
                    'disk',
                ),
            );

        $path =
            $this->requiredString(
                $media->getAttribute(
                    'path',
                ),
            );

        abort_unless(
            Storage::disk(
                $disk,
            )->exists(
                $path,
            ),
            404,
        );

        return Storage::disk(
            $disk,
        )->response(
            $path,
            $this->filename(
                document: $document,
                version: $version,
            ),
            [
                'Content-Type' => 'application/pdf',

                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    public function download(
        string $slug,
    ): StreamedResponse {
        $document =
            $this->publicDocument(
                $slug,
            );

        $version =
            $this->currentVersion(
                $document,
            );

        $media =
            $version->media;

        abort_unless(
            $media instanceof MediaAsset,
            404,
        );

        $disk =
            $this->requiredString(
                $media->getAttribute(
                    'disk',
                ),
            );

        $path =
            $this->requiredString(
                $media->getAttribute(
                    'path',
                ),
            );

        abort_unless(
            Storage::disk(
                $disk,
            )->exists(
                $path,
            ),
            404,
        );

        return Storage::disk(
            $disk,
        )->download(
            $path,
            $this->filename(
                document: $document,
                version: $version,
            ),
            [
                'Content-Type' => 'application/pdf',

                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    /**
     * @return Builder<Document>
     */
    private function publicDocumentsQuery(): Builder
    {
        return Document::query()
            ->where(
                'status',
                DocumentStatus::Published->value,
            )
            ->whereNotNull(
                'published_at',
            )
            ->where(
                'published_at',
                '<=',
                now(),
            )
            ->where(
                'current_version',
                '>',
                0,
            )
            ->whereHas(
                'versions',
                static function (
                    Builder $query,
                ): void {
                    $query
                        ->whereColumn(
                            'document_versions.version',
                            'documents.current_version',
                        )
                        ->whereHas(
                            'media',
                            static function (
                                Builder $mediaQuery,
                            ): void {
                                self::applyPublicPdfRules(
                                    $mediaQuery,
                                );
                            },
                        );
                },
            );
    }

    private function publicDocument(
        string $slug,
    ): Document {
        return $this->publicDocumentsQuery()
            ->where(
                'slug',
                $slug,
            )
            ->with([
                'category',
            ])
            ->firstOrFail();
    }

    private function currentVersion(
        Document $document,
    ): DocumentVersion {
        return DocumentVersion::query()
            ->where(
                'document_id',
                (int) $document->getKey(),
            )
            ->where(
                'version',
                $document->current_version,
            )
            ->whereHas(
                'media',
                static function (
                    Builder $query,
                ): void {
                    self::applyPublicPdfRules(
                        $query,
                    );
                },
            )
            ->with([
                'media',
                'uploader',
            ])
            ->firstOrFail();
    }

    /**
     * @param  Builder<Model>  $query
     */
    private static function applyPublicPdfRules(
        Builder $query,
    ): void {
        $query
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
            ->where(
                'disk',
                '!=',
                '',
            )
            ->whereNotNull(
                'path',
            )
            ->where(
                'path',
                '!=',
                '',
            );
    }

    private function filename(
        Document $document,
        DocumentVersion $version,
    ): string {
        $baseName =
            Str::slug(
                $document->title,
            );

        if ($baseName === '') {
            $baseName =
                'document';
        }

        return $baseName
            .'-v'
            .$version->version
            .'.pdf';
    }

    private function requiredString(
        mixed $value,
    ): string {
        abort_unless(
            is_string(
                $value,
            )
            && trim(
                $value,
            ) !== '',
            404,
        );

        return trim(
            $value,
        );
    }
}
