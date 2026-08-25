<?php

use App\Enums\DocumentStatus;
use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $documentAttributes
 * @param  array<string, mixed>  $mediaAttributes
 * @param  array<string, mixed>  $versionAttributes
 * @return array{
 *     document: Document,
 *     media: MediaAsset,
 *     version: DocumentVersion
 * }
 */
function createPublicDocumentFixtureForPublicDocumentTest(
    array $documentAttributes = [],
    array $mediaAttributes = [],
    array $versionAttributes = [],
    bool $storeFile = true,
): array {
    $uuid =
        Str::uuid()->toString();

    $path =
        'media/testing/'.$uuid.'.pdf';

    $media =
        MediaAsset::factory()
            ->document()
            ->create(
                array_replace(
                    [
                        'type' => MediaType::Document->value,

                        'source' => MediaSource::Upload->value,

                        'visibility' => MediaVisibility::Public->value,

                        'title' => 'Public Document PDF',

                        'disk' => 'public',

                        'directory' => 'media/testing',

                        'stored_name' => $uuid.'.pdf',

                        'original_name' => 'public-document.pdf',

                        'path' => $path,

                        'external_url' => null,

                        'mime_type' => 'application/pdf',

                        'extension' => 'pdf',

                        'size_bytes' => 1024,

                        'width' => null,

                        'height' => null,
                    ],
                    $mediaAttributes,
                ),
            );

    if ($storeFile) {
        $disk =
            $media->getAttribute(
                'disk',
            );

        $mediaPath =
            $media->getAttribute(
                'path',
            );

        if (
            is_string($disk)
            && trim($disk) !== ''
            && is_string($mediaPath)
            && trim($mediaPath) !== ''
        ) {
            Storage::disk(
                $disk,
            )->put(
                $mediaPath,
                '%PDF-1.4 public document test',
            );
        }
    }

    $document =
        Document::query()
            ->create(
                array_replace(
                    [
                        'title' => 'Public Document',

                        'slug' => 'public-document',

                        'document_category_id' => null,

                        'description' => 'Public document description.',

                        'document_date' => now()
                            ->subDay()
                            ->toDateString(),

                        'current_version' => 1,

                        'status' => DocumentStatus::Published->value,

                        'published_at' => now()->subHour(),

                        'archived_at' => null,

                        'seo_title' => null,

                        'seo_description' => null,

                        'created_by' => null,

                        'updated_by' => null,

                        'published_by' => null,

                        'archived_by' => null,
                    ],
                    $documentAttributes,
                ),
            );

    $version =
        DocumentVersion::query()
            ->create(
                array_replace(
                    [
                        'document_id' => (int) $document->getKey(),

                        'media_asset_id' => (int) $media->getKey(),

                        'version' => 1,

                        'version_label' => 'Version 1.0',

                        'change_note' => 'Initial public PDF version.',

                        'uploaded_by' => null,
                    ],
                    $versionAttributes,
                ),
            );

    return [
        'document' => $document,

        'media' => $media,

        'version' => $version,
    ];
}

beforeEach(function (): void {
    Storage::fake(
        'public',
    );
});

/*
|--------------------------------------------------------------------------
| Public Index
|--------------------------------------------------------------------------
*/

test(
    'guests can access the public document index',
    function (): void {
        createPublicDocumentFixtureForPublicDocumentTest([
            'title' => 'Public Annual Report',

            'slug' => 'public-annual-report',
        ]);

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertSee(
                'Documents',
            )
            ->assertSee(
                'Public Annual Report',
            );
    },
);

test(
    'public document index only shows currently published documents',
    function (): void {
        $published =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Current Published Document',

                'slug' => 'current-published-document',

                'status' => DocumentStatus::Published->value,

                'published_at' => now()->subHour(),
            ])['document'];

        $future =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Future Document',

                'slug' => 'future-document',

                'status' => DocumentStatus::Published->value,

                'published_at' => now()->addDay(),
            ])['document'];

        $draft =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Draft Document',

                'slug' => 'draft-document',

                'status' => DocumentStatus::Draft->value,

                'published_at' => now()->subDay(),
            ])['document'];

        $archived =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Archived Document',

                'slug' => 'archived-document',

                'status' => DocumentStatus::Archived->value,

                'published_at' => now()->subDay(),

                'archived_at' => now(),
            ])['document'];

        $withoutPublicationDate =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Document Without Publication Date',

                'slug' => 'document-without-publication-date',

                'status' => DocumentStatus::Published->value,

                'published_at' => null,
            ])['document'];

        $response =
            $this->get(
                route(
                    'documents.index',
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                $published->title,
            )
            ->assertDontSee(
                $future->title,
            )
            ->assertDontSee(
                $draft->title,
            )
            ->assertDontSee(
                $archived->title,
            )
            ->assertDontSee(
                $withoutPublicationDate->title,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public Detail
|--------------------------------------------------------------------------
*/

test(
    'guests can view a currently published document',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Public Document Detail',

                'slug' => 'public-document-detail',

                'description' => 'Public document detail description.',
            ]);

        $document =
            $fixture['document'];

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $document->slug,
                ],
            ),
        )
            ->assertOk()
            ->assertSee(
                'Public Document Detail',
            )
            ->assertSee(
                'Public document detail description.',
            )
            ->assertSee(
                'Version 1',
            )
            ->assertSee(
                route(
                    'documents.view',
                    [
                        'slug' => $document->slug,
                    ],
                ),
            )
            ->assertSee(
                route(
                    'documents.download',
                    [
                        'slug' => $document->slug,
                    ],
                ),
            );
    },
);

/*
|--------------------------------------------------------------------------
| Workflow Visibility Protection
|--------------------------------------------------------------------------
*/

test(
    'draft documents cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'slug' => 'hidden-draft-document',

                'status' => DocumentStatus::Draft->value,

                'published_at' => now()->subHour(),
            ]);

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'archived documents cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'slug' => 'hidden-archived-document',

                'status' => DocumentStatus::Archived->value,

                'published_at' => now()->subDay(),

                'archived_at' => now(),
            ]);

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'future scheduled documents cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Future Scheduled Document',

                'slug' => 'future-scheduled-document',

                'status' => DocumentStatus::Published->value,

                'published_at' => now()->addDay(),
            ]);

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'Future Scheduled Document',
            );
    },
);

test(
    'published documents without a publication date cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'slug' => 'published-document-with-no-date',

                'status' => DocumentStatus::Published->value,

                'published_at' => null,
            ]);

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'soft deleted published documents are hidden publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Deleted Public Document',

                'slug' => 'deleted-public-document',
            ]);

        $fixture['document']->delete();

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'Deleted Public Document',
            );

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => 'deleted-public-document',
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Current Version Security
|--------------------------------------------------------------------------
*/

test(
    'documents without a valid current version are hidden publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Broken Current Version',

                'slug' => 'broken-current-version',
            ]);

        $fixture['document']
            ->forceFill([
                'current_version' => 99,
            ])
            ->save();

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'Broken Current Version',
            );

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => 'broken-current-version',
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'a private current pdf hides the document even when an older public version exists',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Protected Current PDF',

                'slug' => 'protected-current-pdf',
            ]);

        $privateUuid =
            Str::uuid()->toString();

        $privateMedia =
            MediaAsset::factory()
                ->document()
                ->create([
                    'type' => MediaType::Document->value,

                    'source' => MediaSource::Upload->value,

                    'visibility' => MediaVisibility::Internal->value,

                    'disk' => 'public',

                    'directory' => 'media/testing',

                    'stored_name' => $privateUuid.'.pdf',

                    'original_name' => 'private-current.pdf',

                    'path' => 'media/testing/'.$privateUuid.'.pdf',

                    'mime_type' => 'application/pdf',

                    'extension' => 'pdf',
                ]);

        DocumentVersion::query()
            ->create([
                'document_id' => (int) $fixture['document']->getKey(),

                'media_asset_id' => (int) $privateMedia->getKey(),

                'version' => 2,

                'version_label' => 'Version 2.0',

                'change_note' => 'Private replacement.',

                'uploaded_by' => null,
            ]);

        $fixture['document']
            ->forceFill([
                'current_version' => 2,
            ])
            ->save();

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'Protected Current PDF',
            );

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => 'protected-current-pdf',
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'a soft deleted current pdf hides the document publicly',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Deleted PDF Document',

                'slug' => 'deleted-pdf-document',
            ]);

        $fixture['media']->delete();

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'Deleted PDF Document',
            );

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => 'deleted-pdf-document',
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| View And Download
|--------------------------------------------------------------------------
*/

test(
    'guests can view the current public pdf inline',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Viewable Report',

                'slug' => 'viewable-report',
            ]);

        $this->get(
            route(
                'documents.view',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/pdf',
            )
            ->assertHeader(
                'content-disposition',
                'inline; filename=viewable-report-v1.pdf',
            );
    },
);

test(
    'guests can download the current public pdf',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Downloadable Report',

                'slug' => 'downloadable-report',
            ]);

        $this->get(
            route(
                'documents.download',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/pdf',
            )
            ->assertHeader(
                'content-disposition',
                'attachment; filename=downloadable-report-v1.pdf',
            );
    },
);

test(
    'view and download return not found when the physical pdf file is missing',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest(
                [
                    'title' => 'Missing Physical PDF',

                    'slug' => 'missing-physical-pdf',
                ],
                storeFile: false,
            );

        $this->get(
            route(
                'documents.view',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();

        $this->get(
            route(
                'documents.download',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Stable Public URL
|--------------------------------------------------------------------------
*/

test(
    'replacing the current pdf keeps the public document urls stable',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Stable Document',

                'slug' => 'stable-document',
            ]);

        $document =
            $fixture['document'];

        $detailUrl =
            route(
                'documents.show',
                [
                    'slug' => $document->slug,
                ],
            );

        $viewUrl =
            route(
                'documents.view',
                [
                    'slug' => $document->slug,
                ],
            );

        $downloadUrl =
            route(
                'documents.download',
                [
                    'slug' => $document->slug,
                ],
            );

        $replacementUuid =
            Str::uuid()->toString();

        $replacementPath =
            'media/testing/'.$replacementUuid.'.pdf';

        $replacement =
            MediaAsset::factory()
                ->document()
                ->create([
                    'type' => MediaType::Document->value,

                    'source' => MediaSource::Upload->value,

                    'visibility' => MediaVisibility::Public->value,

                    'disk' => 'public',

                    'directory' => 'media/testing',

                    'stored_name' => $replacementUuid.'.pdf',

                    'original_name' => 'replacement.pdf',

                    'path' => $replacementPath,

                    'mime_type' => 'application/pdf',

                    'extension' => 'pdf',
                ]);

        Storage::disk(
            'public',
        )->put(
            $replacementPath,
            '%PDF-1.4 replacement document',
        );

        DocumentVersion::query()
            ->create([
                'document_id' => (int) $document->getKey(),

                'media_asset_id' => (int) $replacement->getKey(),

                'version' => 2,

                'version_label' => 'Version 2.0',

                'change_note' => 'Replacement PDF.',

                'uploaded_by' => null,
            ]);

        $document
            ->forceFill([
                'current_version' => 2,
            ])
            ->save();

        $document->refresh();

        expect(
            route(
                'documents.show',
                [
                    'slug' => $document->slug,
                ],
            ),
        )->toBe(
            $detailUrl,
        )
            ->and(
                route(
                    'documents.view',
                    [
                        'slug' => $document->slug,
                    ],
                ),
            )
            ->toBe(
                $viewUrl,
            )
            ->and(
                route(
                    'documents.download',
                    [
                        'slug' => $document->slug,
                    ],
                ),
            )
            ->toBe(
                $downloadUrl,
            );

        $this->get(
            $detailUrl,
        )
            ->assertOk()
            ->assertSee(
                'Version 2',
            );

        $this->get(
            $viewUrl,
        )
            ->assertOk()
            ->assertHeader(
                'content-disposition',
                'inline; filename=stable-document-v2.pdf',
            );
    },
);

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/

test(
    'public document detail renders custom seo metadata',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Normal Document Title',

                'slug' => 'seo-document',

                'description' => 'Normal document description.',

                'seo_title' => 'Special Document SEO Title',

                'seo_description' => 'Special SEO description for this document.',
            ]);

        $document =
            $fixture['document'];

        $url =
            route(
                'documents.show',
                [
                    'slug' => $document->slug,
                ],
            );

        $this->get(
            $url,
        )
            ->assertOk()
            ->assertSee(
                '<title>Special Document SEO Title</title>',
                false,
            )
            ->assertSee(
                'Special SEO description for this document.',
            )
            ->assertSee(
                'rel="canonical"',
                false,
            )
            ->assertSee(
                $url,
            )
            ->assertSee(
                'property="og:title"',
                false,
            )
            ->assertSee(
                'property="og:description"',
                false,
            )
            ->assertSee(
                'property="og:url"',
                false,
            );
    },
);

test(
    'public document detail falls back to title and description for seo',
    function (): void {
        $fixture =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Fallback Document SEO Title',

                'slug' => 'fallback-document-seo',

                'description' => '<p>Fallback document SEO description.</p>',

                'seo_title' => null,

                'seo_description' => null,
            ]);

        $this->get(
            route(
                'documents.show',
                [
                    'slug' => $fixture['document']->slug,
                ],
            ),
        )
            ->assertOk()
            ->assertSee(
                '<title>Fallback Document SEO Title</title>',
                false,
            )
            ->assertSee(
                'Fallback document SEO description.',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public Ordering
|--------------------------------------------------------------------------
*/

test(
    'public document index shows newer document dates first',
    function (): void {
        $older =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Older Document',

                'slug' => 'older-document',

                'document_date' => now()
                    ->subMonth()
                    ->toDateString(),

                'published_at' => now()->subHour(),
            ])['document'];

        $newer =
            createPublicDocumentFixtureForPublicDocumentTest([
                'title' => 'Newer Document',

                'slug' => 'newer-document',

                'document_date' => now()
                    ->toDateString(),

                'published_at' => now()->subDay(),
            ])['document'];

        $this->get(
            route(
                'documents.index',
            ),
        )
            ->assertOk()
            ->assertSeeInOrder([
                $newer->title,

                $older->title,
            ]);
    },
);
