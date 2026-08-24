<?php

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\User;
use App\Services\NewsWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

/*
|--------------------------------------------------------------------------
| Role Permissions
|--------------------------------------------------------------------------
*/

test(
    'news workflow roles have the expected permissions',
    function (): void {
        $editor =
            User::factory()->create();

        $publisher =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $publisher->assignRole(
            'Publisher',
        );

        expect(
            $editor->can(
                'news.submit',
            ),
        )->toBeTrue()
            ->and(
                $editor->can(
                    'news.request-changes',
                ),
            )->toBeFalse()
            ->and(
                $editor->can(
                    'news.approve',
                ),
            )->toBeFalse()
            ->and(
                $editor->can(
                    'news.publish',
                ),
            )->toBeFalse()
            ->and(
                $editor->can(
                    'news.archive',
                ),
            )->toBeFalse();

        expect(
            $publisher->can(
                'news.submit',
            ),
        )->toBeTrue()
            ->and(
                $publisher->can(
                    'news.request-changes',
                ),
            )->toBeTrue()
            ->and(
                $publisher->can(
                    'news.approve',
                ),
            )->toBeTrue()
            ->and(
                $publisher->can(
                    'news.publish',
                ),
            )->toBeTrue()
            ->and(
                $publisher->can(
                    'news.archive',
                ),
            )->toBeTrue();
    },
);

/*
|--------------------------------------------------------------------------
| Submit for Review
|--------------------------------------------------------------------------
*/

test(
    'a content editor can submit a draft news article for review',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Draft->value,

                'submitted_at' => null,

                'submitted_by' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->submit(
                news: $news,
                actor: $editor,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Submitted,
        )
            ->and(
                $updated->submitted_at,
            )->not->toBeNull()
            ->and(
                $updated->submitted_by,
            )->toBe(
                $editor->id,
            );
    },
);

test(
    'submitting a draft creates a revision snapshot',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Draft->value,
            ]);

        app(
            NewsWorkflowService::class,
        )->submit(
            news: $news,
            actor: $editor,
        );

        expect(
            $news->revisions()
                ->count(),
        )->toBe(1);

        $revision =
            $news->revisions()
                ->first();

        expect(
            $revision,
        )->not->toBeNull();

        expect(
            $revision?->status,
        )->toBe(
            NewsStatus::Draft,
        )
            ->and(
                $revision?->reason,
            )->toBe(
                'Snapshot before submission for review.',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Request Changes
|--------------------------------------------------------------------------
*/

test(
    'a publisher can request changes from a submitted article',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Submitted->value,

                'changes_requested_at' => null,

                'changes_requested_by' => null,

                'change_request_note' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->requestChanges(
                news: $news,
                actor: $publisher,
                note: 'Please correct the introduction and verify the publication date.',
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::ChangesRequested,
        )
            ->and(
                $updated->changes_requested_at,
            )->not->toBeNull()
            ->and(
                $updated->changes_requested_by,
            )->toBe(
                $publisher->id,
            )
            ->and(
                $updated->change_request_note,
            )->toBe(
                'Please correct the introduction and verify the publication date.',
            );
    },
);

test(
    'a change request requires a review note',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Submitted->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->requestChanges(
                news: $news,
                actor: $publisher,
                note: '   ',
            ),
        )->toThrow(
            ValidationException::class,
        );

        $news->refresh();

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Submitted,
        );
    },
);

/*
|--------------------------------------------------------------------------
| Resubmit
|--------------------------------------------------------------------------
*/

test(
    'a content editor can resubmit an article after changes are requested',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $reviewer =
            User::factory()->create();

        $news =
            News::factory()->create([
                'status' => NewsStatus::ChangesRequested->value,

                'changes_requested_at' => now(),

                'changes_requested_by' => $reviewer->id,

                'change_request_note' => 'Please update the article.',
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->submit(
                news: $news,
                actor: $editor,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Submitted,
        )
            ->and(
                $updated->submitted_at,
            )->not->toBeNull()
            ->and(
                $updated->submitted_by,
            )->toBe(
                $editor->id,
            )
            ->and(
                $updated->changes_requested_at,
            )->toBeNull()
            ->and(
                $updated->changes_requested_by,
            )->toBeNull()
            ->and(
                $updated->change_request_note,
            )->toBeNull();
    },
);

/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

test(
    'a publisher can approve a submitted article',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Submitted->value,

                'approved_at' => null,

                'approved_by' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->approve(
                news: $news,
                actor: $publisher,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Approved,
        )
            ->and(
                $updated->approved_at,
            )->not->toBeNull()
            ->and(
                $updated->approved_by,
            )->toBe(
                $publisher->id,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Publish
|--------------------------------------------------------------------------
*/

test(
    'a publisher can publish an approved article',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Approved->value,

                'published_at' => null,

                'published_by' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->publish(
                news: $news,
                actor: $publisher,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Published,
        )
            ->and(
                $updated->published_at,
            )->not->toBeNull()
            ->and(
                $updated->published_by,
            )->toBe(
                $publisher->id,
            );
    },
);

test(
    'publishing preserves a future planned publication time',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $plannedPublication =
            now()
                ->addDay()
                ->startOfMinute();

        $news =
            News::factory()->create([
                'status' => NewsStatus::Approved->value,

                'published_at' => $plannedPublication,

                'published_by' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->publish(
                news: $news,
                actor: $publisher,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Published,
        )
            ->and(
                $updated->published_at?->format(
                    'Y-m-d H:i:s',
                ),
            )->toBe(
                $plannedPublication->format(
                    'Y-m-d H:i:s',
                ),
            )
            ->and(
                $updated->published_by,
            )->toBe(
                $publisher->id,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Archive
|--------------------------------------------------------------------------
*/

test(
    'a publisher can archive a published article',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Published->value,

                'published_at' => now()
                    ->subMinute(),

                'archived_at' => null,

                'archived_by' => null,
            ]);

        $updated =
            app(
                NewsWorkflowService::class,
            )->archive(
                news: $news,
                actor: $publisher,
            );

        expect(
            $updated->status,
        )->toBe(
            NewsStatus::Archived,
        )
            ->and(
                $updated->archived_at,
            )->not->toBeNull()
            ->and(
                $updated->archived_by,
            )->toBe(
                $publisher->id,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Complete Workflow
|--------------------------------------------------------------------------
*/

test(
    'a news article can complete the full review workflow',
    function (): void {
        $editor =
            User::factory()->create();

        $publisher =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $publisher->assignRole(
            'Publisher',
        );

        $service =
            app(
                NewsWorkflowService::class,
            );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Draft->value,

                'published_at' => null,
            ]);

        /*
         * Draft -> Submitted
         */
        $news =
            $service->submit(
                news: $news,
                actor: $editor,
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Submitted,
        );

        /*
         * Submitted -> Changes Requested
         */
        $news =
            $service->requestChanges(
                news: $news,
                actor: $publisher,
                note: 'Please update the article before approval.',
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::ChangesRequested,
        );

        /*
         * Changes Requested -> Submitted
         */
        $news =
            $service->submit(
                news: $news,
                actor: $editor,
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Submitted,
        );

        /*
         * Submitted -> Approved
         */
        $news =
            $service->approve(
                news: $news,
                actor: $publisher,
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Approved,
        );

        /*
         * Approved -> Published
         */
        $news =
            $service->publish(
                news: $news,
                actor: $publisher,
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Published,
        );

        /*
         * Published -> Archived
         */
        $news =
            $service->archive(
                news: $news,
                actor: $publisher,
            );

        expect(
            $news->status,
        )->toBe(
            NewsStatus::Archived,
        );
    },
);

/*
|--------------------------------------------------------------------------
| Invalid Transitions
|--------------------------------------------------------------------------
*/

test(
    'a draft article cannot be approved directly',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Draft->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->approve(
                news: $news,
                actor: $publisher,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            $news->fresh()?->status,
        )->toBe(
            NewsStatus::Draft,
        );
    },
);

test(
    'a submitted article cannot be published before approval',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Submitted->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->publish(
                news: $news,
                actor: $publisher,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            $news->fresh()?->status,
        )->toBe(
            NewsStatus::Submitted,
        );
    },
);

test(
    'an approved article cannot be archived before publication',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Approved->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->archive(
                news: $news,
                actor: $publisher,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            $news->fresh()?->status,
        )->toBe(
            NewsStatus::Approved,
        );
    },
);

/*
|--------------------------------------------------------------------------
| Permission Enforcement
|--------------------------------------------------------------------------
*/

test(
    'a content editor cannot approve a submitted article',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Submitted->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->approve(
                news: $news,
                actor: $editor,
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect(
            $news->fresh()?->status,
        )->toBe(
            NewsStatus::Submitted,
        );
    },
);

test(
    'a content editor cannot publish an approved article',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        $news =
            News::factory()->create([
                'status' => NewsStatus::Approved->value,
            ]);

        expect(
            fn () => app(
                NewsWorkflowService::class,
            )->publish(
                news: $news,
                actor: $editor,
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect(
            $news->fresh()?->status,
        )->toBe(
            NewsStatus::Approved,
        );
    },
);
