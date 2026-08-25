<?php

use App\Enums\DocumentStatus;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Documents\DocumentCreate;
use App\Livewire\Admin\Documents\DocumentEdit;
use App\Livewire\Admin\Documents\DocumentIndex;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\DocumentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function documentLivewireUser(
    string $role,
): User {
    $user =
        User::factory()->create();

    $user->assignRole(
        $role,
    );

    return $user;
}

function documentLivewirePdf(
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

function documentLivewireDraft(
    User $actor,
    string $title = 'Livewire Document',
): Document {
    return app(
        DocumentService::class,
    )->create(
        actor: $actor,
        title: $title,
    );
}

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

it(
    'redirects guests away from the document admin route',
    function (): void {
        $this->get(
            route(
                'admin.documents.index',
            ),
        )->assertRedirect(
            route(
                'login',
            ),
        );
    },
);

it(
    'allows an auditor to view the document index',
    function (): void {
        $auditor =
            documentLivewireUser(
                'Auditor',
            );

        $this->actingAs(
            $auditor,
        );

        Livewire::test(
            DocumentIndex::class,
        )->assertOk();
    },
);

it(
    'allows a content editor to create a document',
    function (): void {
        $editor =
            documentLivewireUser(
                'Content Editor',
            );

        $this->actingAs(
            $editor,
        );

        $component =
            Livewire::test(
                DocumentCreate::class,
            )
                ->set(
                    'title',
                    'Annual Report 2026',
                )
                ->set(
                    'documentDate',
                    '2026-08-25',
                )
                ->set(
                    'description',
                    'Document created through Livewire.',
                )
                ->call(
                    'save',
                )
                ->assertHasNoErrors();

        $document =
            Document::query()
                ->where(
                    'title',
                    'Annual Report 2026',
                )
                ->firstOrFail();

        expect($document->status)
            ->toBe(
                DocumentStatus::Draft,
            )
            ->and($document->slug)
            ->toBe(
                'annual-report-2026',
            )
            ->and($document->current_version)
            ->toBe(0)
            ->and($document->created_by)
            ->toBe(
                $editor->id,
            );

        $component->assertRedirect(
            route(
                'admin.documents.edit',
                [
                    'document' => $document->id,
                ],
            ),
        );
    },
);

it(
    'validates the required document title',
    function (): void {
        $editor =
            documentLivewireUser(
                'Content Editor',
            );

        $this->actingAs(
            $editor,
        );

        Livewire::test(
            DocumentCreate::class,
        )
            ->set(
                'title',
                '',
            )
            ->call(
                'save',
            )
            ->assertHasErrors([
                'title' => 'required',
            ]);

        expect(
            Document::query()
                ->count(),
        )->toBe(0);
    },
);

it(
    'prevents an auditor from opening document edit',
    function (): void {
        $administrator =
            documentLivewireUser(
                'Site Administrator',
            );

        $document =
            documentLivewireDraft(
                $administrator,
                'Auditor Security Test',
            );

        $auditor =
            documentLivewireUser(
                'Auditor',
            );

        $this->actingAs(
            $auditor,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document,
            ],
        )->assertForbidden();
    },
);

it(
    'allows a media operator to add a pdf version to a draft document',
    function (): void {
        $operator =
            documentLivewireUser(
                'Media Operator',
            );

        $document =
            documentLivewireDraft(
                $operator,
                'Media Operator Document',
            );

        $media =
            documentLivewirePdf(
                $operator,
            );

        $this->actingAs(
            $operator,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document,
            ],
        )
            ->set(
                'selectedMediaId',
                (string) $media->id,
            )
            ->set(
                'versionLabel',
                'Version 1.0',
            )
            ->set(
                'changeNote',
                'Initial PDF version.',
            )
            ->call(
                'addVersion',
            )
            ->assertHasNoErrors();

        $version =
            DocumentVersion::query()
                ->where(
                    'document_id',
                    $document->id,
                )
                ->firstOrFail();

        expect($version->version)
            ->toBe(1)
            ->and($version->media_asset_id)
            ->toBe(
                $media->id,
            )
            ->and($version->version_label)
            ->toBe(
                'Version 1.0',
            )
            ->and($document->refresh()->current_version)
            ->toBe(1);
    },
);

it(
    'prevents a content editor from publishing a document',
    function (): void {
        $editor =
            documentLivewireUser(
                'Content Editor',
            );

        $document =
            documentLivewireDraft(
                $editor,
                'Editor Publish Test',
            );

        app(
            DocumentService::class,
        )->addVersion(
            document: $document,
            actor: $editor,
            media: documentLivewirePdf(
                $editor,
            ),
        );

        $this->actingAs(
            $editor,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document,
            ],
        )
            ->call(
                'publish',
            )
            ->assertForbidden();

        expect(
            $document->refresh()->status,
        )->toBe(
            DocumentStatus::Draft,
        );
    },
);

it(
    'prevents a media operator from publishing a document',
    function (): void {
        $operator =
            documentLivewireUser(
                'Media Operator',
            );

        $document =
            documentLivewireDraft(
                $operator,
                'Operator Publish Test',
            );

        app(
            DocumentService::class,
        )->addVersion(
            document: $document,
            actor: $operator,
            media: documentLivewirePdf(
                $operator,
            ),
        );

        $this->actingAs(
            $operator,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document,
            ],
        )
            ->call(
                'publish',
            )
            ->assertForbidden();

        expect(
            $document->refresh()->status,
        )->toBe(
            DocumentStatus::Draft,
        );
    },
);

it(
    'allows a publisher to publish and archive a document',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Publisher Workflow Test',
            );

        app(
            DocumentService::class,
        )->addVersion(
            document: $document,
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        $this->actingAs(
            $publisher,
        );

        $component =
            Livewire::test(
                DocumentEdit::class,
                [
                    'document' => $document,
                ],
            );

        $component
            ->call(
                'publish',
            )
            ->assertHasNoErrors();

        $document->refresh();

        expect($document->status)
            ->toBe(
                DocumentStatus::Published,
            )
            ->and($document->published_by)
            ->toBe(
                $publisher->id,
            );

        $component
            ->call(
                'archive',
            )
            ->assertHasNoErrors();

        $document->refresh();

        expect($document->status)
            ->toBe(
                DocumentStatus::Archived,
            )
            ->and($document->archived_by)
            ->toBe(
                $publisher->id,
            );
    },
);

it(
    'allows a publisher to replace the live pdf while preserving the document slug',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Stable Public PDF',
            );

        $originalSlug =
            $document->slug;

        app(
            DocumentService::class,
        )->addVersion(
            document: $document,
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        app(
            DocumentService::class,
        )->publish(
            document: $document->refresh(),
            actor: $publisher,
        );

        $replacement =
            documentLivewirePdf(
                $publisher,
            );

        $this->actingAs(
            $publisher,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->set(
                'selectedMediaId',
                (string) $replacement->id,
            )
            ->set(
                'versionLabel',
                'Version 2.0',
            )
            ->set(
                'changeNote',
                'Replacement live PDF.',
            )
            ->call(
                'addVersion',
            )
            ->assertHasNoErrors();

        $document->refresh();

        expect($document->status)
            ->toBe(
                DocumentStatus::Published,
            )
            ->and($document->current_version)
            ->toBe(2)
            ->and($document->slug)
            ->toBe(
                $originalSlug,
            )
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
    'prevents a media operator from replacing the live pdf',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Protected Live PDF',
            );

        app(
            DocumentService::class,
        )->addVersion(
            document: $document,
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        app(
            DocumentService::class,
        )->publish(
            document: $document->refresh(),
            actor: $publisher,
        );

        $operator =
            documentLivewireUser(
                'Media Operator',
            );

        $replacement =
            documentLivewirePdf(
                $operator,
            );

        $this->actingAs(
            $operator,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->set(
                'selectedMediaId',
                (string) $replacement->id,
            )
            ->call(
                'addVersion',
            )
            ->assertForbidden();

        expect(
            $document->refresh()->current_version,
        )->toBe(1);
    },
);

/*
|--------------------------------------------------------------------------
| Restore Previous Versions
|--------------------------------------------------------------------------
*/

it(
    'allows a media operator to restore a previous version on a draft document',
    function (): void {
        $operator =
            documentLivewireUser(
                'Media Operator',
            );

        $document =
            documentLivewireDraft(
                $operator,
                'Draft Livewire Restore',
            );

        $service =
            app(
                DocumentService::class,
            );

        $first =
            $service->addVersion(
                document: $document,
                actor: $operator,
                media: documentLivewirePdf(
                    $operator,
                ),
            );

        $service->addVersion(
            document: $document->refresh(),
            actor: $operator,
            media: documentLivewirePdf(
                $operator,
            ),
        );

        expect(
            $document->refresh()->current_version,
        )->toBe(2);

        $this->actingAs(
            $operator,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->assertSee(
                'Restore v1',
            )
            ->call(
                'restoreVersion',
                $first->id,
            )
            ->assertHasNoErrors();

        expect(
            $document->refresh()->current_version,
        )->toBe(1);
    },
);

it(
    'allows a publisher to restore a previous live pdf version',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Published Livewire Restore',
            );

        $service =
            app(
                DocumentService::class,
            );

        $first =
            $service->addVersion(
                document: $document,
                actor: $publisher,
                media: documentLivewirePdf(
                    $publisher,
                ),
            );

        $service->addVersion(
            document: $document->refresh(),
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        $service->publish(
            document: $document->refresh(),
            actor: $publisher,
        );

        $this->actingAs(
            $publisher,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->assertSee(
                'Restore v1',
            )
            ->call(
                'restoreVersion',
                $first->id,
            )
            ->assertHasNoErrors()
            ->assertSee(
                'PDF version 1 restored successfully.',
            );

        $document->refresh();

        expect($document->current_version)
            ->toBe(1)
            ->and($document->status)
            ->toBe(
                DocumentStatus::Published,
            );
    },
);

it(
    'prevents a media operator from restoring a previous live pdf version',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Protected Livewire Restore',
            );

        $service =
            app(
                DocumentService::class,
            );

        $first =
            $service->addVersion(
                document: $document,
                actor: $publisher,
                media: documentLivewirePdf(
                    $publisher,
                ),
            );

        $service->addVersion(
            document: $document->refresh(),
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        $service->publish(
            document: $document->refresh(),
            actor: $publisher,
        );

        $operator =
            documentLivewireUser(
                'Media Operator',
            );

        $this->actingAs(
            $operator,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->assertDontSee(
                'Restore v1',
            )
            ->call(
                'restoreVersion',
                $first->id,
            )
            ->assertForbidden();

        expect(
            $document->refresh()->current_version,
        )->toBe(2);
    },
);

it(
    'does not show restore controls for an archived document',
    function (): void {
        $publisher =
            documentLivewireUser(
                'Publisher',
            );

        $document =
            documentLivewireDraft(
                $publisher,
                'Archived Livewire Restore',
            );

        $service =
            app(
                DocumentService::class,
            );

        $service->addVersion(
            document: $document,
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        $service->addVersion(
            document: $document->refresh(),
            actor: $publisher,
            media: documentLivewirePdf(
                $publisher,
            ),
        );

        $published =
            $service->publish(
                document: $document->refresh(),
                actor: $publisher,
            );

        $service->archive(
            document: $published,
            actor: $publisher,
        );

        $this->actingAs(
            $publisher,
        );

        Livewire::test(
            DocumentEdit::class,
            [
                'document' => $document->refresh(),
            ],
        )
            ->assertOk()
            ->assertDontSee(
                'Restore v1',
            );
    },
);
