<?php

use App\Enums\DocumentStatus;
use App\Enums\MediaVisibility;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\DocumentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function documentTestPdf(
    User $actor,
    MediaVisibility $visibility = MediaVisibility::Public,
): MediaAsset {
    return MediaAsset::factory()
        ->document()
        ->create([
            'visibility' => $visibility->value,

            'uploaded_by' => $actor->id,
        ]);
}

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );

    $this->actor =
        User::factory()->create();

    $this->actor->assignRole(
        'Site Administrator',
    );

    $this->service =
        app(
            DocumentService::class,
        );
});

it(
    'creates a document as draft with a generated slug',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Annual Report 2026',
                description: 'Official annual report.',
            );

        expect($document->status)
            ->toBe(DocumentStatus::Draft)
            ->and($document->slug)
            ->toBe('annual-report-2026')
            ->and($document->current_version)
            ->toBe(0)
            ->and($document->created_by)
            ->toBe($this->actor->id)
            ->and($document->updated_by)
            ->toBe($this->actor->id)
            ->and($document->published_by)
            ->toBeNull()
            ->and($document->archived_by)
            ->toBeNull()
            ->and($document->uuid)
            ->not
            ->toBeNull();
    },
);

it(
    'generates unique document slugs',
    function (): void {
        $first =
            $this->service->create(
                actor: $this->actor,
                title: 'Training Manual',
            );

        $second =
            $this->service->create(
                actor: $this->actor,
                title: 'Training Manual',
            );

        expect($first->slug)
            ->toBe('training-manual')
            ->and($second->slug)
            ->toBe('training-manual-2');
    },
);

it(
    'adds the first pdf version to a draft document',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Policy Document',
            );

        $media =
            documentTestPdf(
                $this->actor,
            );

        $version =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: $media,
                versionLabel: 'Version 1.0',
                changeNote: 'Initial publication version.',
            );

        expect($version->document_id)
            ->toBe($document->id)
            ->and($version->media_asset_id)
            ->toBe($media->id)
            ->and($version->version)
            ->toBe(1)
            ->and($version->version_label)
            ->toBe('Version 1.0')
            ->and($document->refresh()->current_version)
            ->toBe(1);
    },
);

it(
    'increments pdf version numbers sequentially',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Versioned Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        $second =
            $this->service->addVersion(
                document: $document->refresh(),
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        expect($first->version)
            ->toBe(1)
            ->and($second->version)
            ->toBe(2)
            ->and($document->refresh()->current_version)
            ->toBe(2)
            ->and(
                DocumentVersion::query()
                    ->where(
                        'document_id',
                        $document->id,
                    )
                    ->count(),
            )
            ->toBe(2);
    },
);

it(
    'rejects non public pdf media',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Internal PDF Test',
            );

        $media =
            documentTestPdf(
                $this->actor,
                MediaVisibility::Internal,
            );

        expect(
            fn () => $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: $media,
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'rejects non document media as a pdf version',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Invalid Media Test',
            );

        $image =
            MediaAsset::factory()->create([
                'uploaded_by' => $this->actor->id,
            ]);

        expect(
            fn () => $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: $image,
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'does not publish a document without a pdf version',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Empty Document',
            );

        expect(
            fn () => $this->service->publish(
                document: $document,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($document->refresh()->status)
            ->toBe(DocumentStatus::Draft);
    },
);

it(
    'publishes a document with a valid public pdf version',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Public Document',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        expect($published->status)
            ->toBe(DocumentStatus::Published)
            ->and($published->published_at)
            ->not
            ->toBeNull()
            ->and($published->published_by)
            ->toBe($this->actor->id)
            ->and($published->current_version)
            ->toBe(1)
            ->and($published->isPublished())
            ->toBeTrue();
    },
);

it(
    'revalidates current pdf media before publishing',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Revalidation Test',
            );

        $media =
            documentTestPdf(
                $this->actor,
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: $media,
        );

        $media->forceFill([
            'visibility' => MediaVisibility::Internal->value,
        ])->save();

        expect(
            fn () => $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($document->refresh()->status)
            ->toBe(DocumentStatus::Draft);
    },
);

it(
    'locks document metadata editing after publication',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Published Metadata Test',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        expect(
            fn () => $this->service->update(
                document: $published,
                actor: $this->actor,
                title: 'Changed Published Document',
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'allows an authorised publisher to replace the pdf while preserving the stable slug',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Stable Public Document',
            );

        $originalSlug =
            $document->slug;

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $newVersion =
            $this->service->addVersion(
                document: $published,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
                changeNote: 'Updated PDF.',
            );

        expect($newVersion->version)
            ->toBe(2)
            ->and($document->refresh()->current_version)
            ->toBe(2)
            ->and($document->slug)
            ->toBe($originalSlug)
            ->and($document->status)
            ->toBe(DocumentStatus::Published);
    },
);

it(
    'prevents a non publisher from replacing the live pdf',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Protected Live PDF',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $replacement =
            documentTestPdf(
                $editor,
            );

        expect(
            fn () => $this->service->addVersion(
                document: $published,
                actor: $editor,
                media: $replacement,
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect($document->refresh()->current_version)
            ->toBe(1);
    },
);

it(
    'archives a published document',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Archive Document',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $archived =
            $this->service->archive(
                document: $published,
                actor: $this->actor,
            );

        expect($archived->status)
            ->toBe(DocumentStatus::Archived)
            ->and($archived->archived_at)
            ->not
            ->toBeNull()
            ->and($archived->archived_by)
            ->toBe($this->actor->id)
            ->and($archived->isPublished())
            ->toBeFalse();
    },
);

it(
    'prevents adding new versions to archived documents',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Archived Version Test',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $archived =
            $this->service->archive(
                document: $published,
                actor: $this->actor,
            );

        expect(
            fn () => $this->service->addVersion(
                document: $archived,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'does not delete a published document directly',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Delete Protection Document',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        expect(
            fn () => $this->service->delete(
                document: $published,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            Document::query()
                ->whereKey(
                    $document->id,
                )
                ->exists(),
        )->toBeTrue();
    },
);

it(
    'allows an archived document to be soft deleted',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Archived Delete Document',
            );

        $this->service->addVersion(
            document: $document,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $archived =
            $this->service->archive(
                document: $published,
                actor: $this->actor,
            );

        $this->service->delete(
            document: $archived,
            actor: $this->actor,
        );

        expect(
            Document::query()
                ->whereKey(
                    $document->id,
                )
                ->exists(),
        )->toBeFalse()
            ->and(
                Document::withTrashed()
                    ->whereKey(
                        $document->id,
                    )
                    ->exists(),
            )
            ->toBeTrue();
    },
);

/*
|--------------------------------------------------------------------------
| Restore Previous Versions
|--------------------------------------------------------------------------
*/

it(
    'restores a previous pdf version on a draft document',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Draft Restore Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
                versionLabel: 'Version 1.0',
            );

        $second =
            $this->service->addVersion(
                document: $document->refresh(),
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
                versionLabel: 'Version 2.0',
            );

        expect($second->version)
            ->toBe(2)
            ->and($document->refresh()->current_version)
            ->toBe(2);

        $restored =
            $this->service->restoreVersion(
                document: $document->refresh(),
                version: $first,
                actor: $this->actor,
            );

        expect($restored->current_version)
            ->toBe(1)
            ->and($restored->status)
            ->toBe(DocumentStatus::Draft)
            ->and(
                DocumentVersion::query()
                    ->where(
                        'document_id',
                        $document->id,
                    )
                    ->count(),
            )
            ->toBe(2);
    },
);

it(
    'allows an authorised publisher to restore a previous version on a published document',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Published Restore Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        $this->service->addVersion(
            document: $document->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        expect($published->current_version)
            ->toBe(2);

        $restored =
            $this->service->restoreVersion(
                document: $published,
                version: $first,
                actor: $this->actor,
            );

        expect($restored->current_version)
            ->toBe(1)
            ->and($restored->status)
            ->toBe(DocumentStatus::Published);
    },
);

it(
    'prevents a non publisher from restoring a previous live pdf version',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Protected Restore Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        $this->service->addVersion(
            document: $document->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        expect(
            fn () => $this->service->restoreVersion(
                document: $published,
                version: $first,
                actor: $editor,
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect($document->refresh()->current_version)
            ->toBe(2);
    },
);

it(
    'prevents restoring a previous version on an archived document',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Archived Restore Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        $this->service->addVersion(
            document: $document->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $published =
            $this->service->publish(
                document: $document->refresh(),
                actor: $this->actor,
            );

        $archived =
            $this->service->archive(
                document: $published,
                actor: $this->actor,
            );

        expect(
            fn () => $this->service->restoreVersion(
                document: $archived,
                version: $first,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($document->refresh()->current_version)
            ->toBe(2);
    },
);

it(
    'prevents restoring a version that belongs to another document',
    function (): void {
        $firstDocument =
            $this->service->create(
                actor: $this->actor,
                title: 'First Restore Document',
            );

        $this->service->addVersion(
            document: $firstDocument,
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $this->service->addVersion(
            document: $firstDocument->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $secondDocument =
            $this->service->create(
                actor: $this->actor,
                title: 'Second Restore Document',
            );

        $foreignVersion =
            $this->service->addVersion(
                document: $secondDocument,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        expect(
            fn () => $this->service->restoreVersion(
                document: $firstDocument->refresh(),
                version: $foreignVersion,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($firstDocument->refresh()->current_version)
            ->toBe(2);
    },
);

it(
    'revalidates historic pdf media before restoring it',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Restore PDF Revalidation Document',
            );

        $firstMedia =
            documentTestPdf(
                $this->actor,
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: $firstMedia,
            );

        $this->service->addVersion(
            document: $document->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $firstMedia
            ->forceFill([
                'visibility' => MediaVisibility::Internal->value,
            ])
            ->save();

        expect(
            fn () => $this->service->restoreVersion(
                document: $document->refresh(),
                version: $first,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($document->refresh()->current_version)
            ->toBe(2);
    },
);

it(
    'prevents restoring a version whose pdf has been deleted',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Deleted Restore PDF Document',
            );

        $firstMedia =
            documentTestPdf(
                $this->actor,
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: $firstMedia,
            );

        $this->service->addVersion(
            document: $document->refresh(),
            actor: $this->actor,
            media: documentTestPdf(
                $this->actor,
            ),
        );

        $firstMedia->delete();

        expect(
            fn () => $this->service->restoreVersion(
                document: $document->refresh(),
                version: $first,
                actor: $this->actor,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect($document->refresh()->current_version)
            ->toBe(2);
    },
);

it(
    'continues version numbering from the highest historic version after a restore',
    function (): void {
        $document =
            $this->service->create(
                actor: $this->actor,
                title: 'Restore Numbering Document',
            );

        $first =
            $this->service->addVersion(
                document: $document,
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        $second =
            $this->service->addVersion(
                document: $document->refresh(),
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        expect($second->version)
            ->toBe(2);

        $this->service->restoreVersion(
            document: $document->refresh(),
            version: $first,
            actor: $this->actor,
        );

        expect($document->refresh()->current_version)
            ->toBe(1);

        $third =
            $this->service->addVersion(
                document: $document->refresh(),
                actor: $this->actor,
                media: documentTestPdf(
                    $this->actor,
                ),
            );

        expect($third->version)
            ->toBe(3)
            ->and($document->refresh()->current_version)
            ->toBe(3)
            ->and(
                DocumentVersion::query()
                    ->where(
                        'document_id',
                        $document->id,
                    )
                    ->count(),
            )
            ->toBe(3);
    },
);
