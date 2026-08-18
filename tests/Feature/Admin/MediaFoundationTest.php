<?php

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaUploadSecurity;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('media asset automatically receives uuid and enum casts', function (): void {
    $media = MediaAsset::factory()->create([
        'uuid' => null,

        'type' => MediaType::Image->value,

        'source' => MediaSource::Upload->value,

        'visibility' => MediaVisibility::Public->value,
    ]);

    expect($media->uuid)
        ->toBeString()
        ->not->toBeEmpty()

        ->and($media->type)
        ->toBe(MediaType::Image)

        ->and($media->source)
        ->toBe(MediaSource::Upload)

        ->and($media->visibility)
        ->toBe(MediaVisibility::Public)

        ->and($media->isImage())
        ->toBeTrue()

        ->and($media->isPublic())
        ->toBeTrue();
});

test('media asset stores uploader relationship', function (): void {
    $user = User::factory()->create();

    $media = MediaAsset::factory()->create([
        'uploaded_by' => $user->id,
    ]);

    expect($media->uploader)
        ->not->toBeNull()

        ->and($media->uploader?->id)
        ->toBe($user->id);
});

test('media assets support soft deletion', function (): void {
    $media = MediaAsset::factory()->create();

    $mediaId =
        (int) $media->id;

    $media->delete();

    expect(
        MediaAsset::query()->find(
            $mediaId,
        ),
    )->toBeNull();

    expect(
        MediaAsset::withTrashed()->find(
            $mediaId,
        ),
    )->not->toBeNull();

    $this->assertSoftDeleted(
        'media_assets',
        [
            'id' => $mediaId,
        ],
    );
});

test('image upload security only accepts configured image mime types', function (): void {
    $security = app(
        MediaUploadSecurity::class,
    );

    expect(
        $security->uploadsAllowed(
            MediaType::Image,
        ),
    )
        ->toBeTrue()

        ->and(
            $security->allowsMimeType(
                MediaType::Image,
                'image/jpeg',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->allowsMimeType(
                MediaType::Image,
                'image/png',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->allowsMimeType(
                MediaType::Image,
                'image/webp',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->allowsMimeType(
                MediaType::Image,
                'image/svg+xml',
            ),
        )
        ->toBeFalse();
});

test('document uploads only allow pdf in initial media version', function (): void {
    $security = app(
        MediaUploadSecurity::class,
    );

    expect(
        $security->allowsMimeType(
            MediaType::Document,
            'application/pdf',
        ),
    )
        ->toBeTrue()

        ->and(
            $security->allowsMimeType(
                MediaType::Document,
                'text/html',
            ),
        )
        ->toBeFalse();
});

test('direct video uploads are disabled initially', function (): void {
    $security = app(
        MediaUploadSecurity::class,
    );

    expect(
        $security->uploadsAllowed(
            MediaType::Video,
        ),
    )->toBeFalse();
});

test('dangerous executable and web extensions are forbidden', function (): void {
    $security = app(
        MediaUploadSecurity::class,
    );

    expect(
        $security->isForbiddenExtension(
            'php',
        ),
    )
        ->toBeTrue()

        ->and(
            $security->isForbiddenExtension(
                '.phtml',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->isForbiddenExtension(
                'html',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->isForbiddenExtension(
                'svg',
            ),
        )
        ->toBeTrue()

        ->and(
            $security->isForbiddenExtension(
                'jpg',
            ),
        )
        ->toBeFalse();
});

test('media operator receives full media permissions', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    expect(
        $operator->can('media.view'),
    )
        ->toBeTrue()

        ->and(
            $operator->can('media.upload'),
        )
        ->toBeTrue()

        ->and(
            $operator->can('media.update'),
        )
        ->toBeTrue()

        ->and(
            $operator->can('media.replace'),
        )
        ->toBeTrue()

        ->and(
            $operator->can('media.delete'),
        )
        ->toBeTrue();
});

test('content editor cannot delete or replace media', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    expect(
        $editor->can('media.view'),
    )
        ->toBeTrue()

        ->and(
            $editor->can('media.upload'),
        )
        ->toBeTrue()

        ->and(
            $editor->can('media.update'),
        )
        ->toBeTrue()

        ->and(
            $editor->can('media.replace'),
        )
        ->toBeFalse()

        ->and(
            $editor->can('media.delete'),
        )
        ->toBeFalse();
});
