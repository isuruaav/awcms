<?php

use App\Enums\MediaType;
use App\Enums\MediaVariantPreset;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaImageVariantService;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );

    Storage::fake(
        'public',
    );

    Storage::fake(
        'local',
    );
});

test('uploaded image can generate thumbnail and medium variants', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'large-photo.jpg',
                1600,
                1200,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $variants = app(
        MediaImageVariantService::class,
    )->generate(
        $media,
    );

    expect($variants)
        ->toHaveCount(2);

    $thumbnail = $media
        ->variant(
            MediaVariantPreset::Thumbnail,
        );

    $medium = $media
        ->variant(
            MediaVariantPreset::Medium,
        );

    expect($thumbnail)
        ->not->toBeNull()

        ->and($medium)
        ->not->toBeNull();

    expect($thumbnail?->name)
        ->toBe(
            MediaVariantPreset::Thumbnail,
        )

        ->and($thumbnail?->mime_type)
        ->toBe('image/webp')

        ->and($thumbnail?->extension)
        ->toBe('webp')

        ->and($thumbnail?->width)
        ->toBeLessThanOrEqual(480)

        ->and($thumbnail?->height)
        ->toBeLessThanOrEqual(480);

    expect($medium?->width)
        ->toBeLessThanOrEqual(1280)

        ->and($medium?->height)
        ->toBeLessThanOrEqual(1280);

    Storage::disk(
        'public',
    )->assertExists(
        (string) $thumbnail?->path,
    );

    Storage::disk(
        'public',
    )->assertExists(
        (string) $medium?->path,
    );
});

test('image variants preserve aspect ratio without upscaling small image', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'small.jpg',
                200,
                100,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    app(
        MediaImageVariantService::class,
    )->generate(
        $media,
    );

    $thumbnail = $media
        ->variant(
            MediaVariantPreset::Thumbnail,
        );

    $medium = $media
        ->variant(
            MediaVariantPreset::Medium,
        );

    expect($thumbnail?->width)
        ->toBe(200)

        ->and($thumbnail?->height)
        ->toBe(100)

        ->and($medium?->width)
        ->toBe(200)

        ->and($medium?->height)
        ->toBe(100);
});

test('private image variants remain on private disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'private.jpg',
                1000,
                800,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Internal,

        actor: $operator,
    );

    app(
        MediaImageVariantService::class,
    )->generate(
        $media,
    );

    foreach (
        $media->variants()->get() as $variant
    ) {
        expect($variant->disk)
            ->toBe('local');

        Storage::disk(
            'local',
        )->assertExists(
            (string) $variant->path,
        );
    }
});

test('regenerating variants replaces old physical variant files', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'regenerate.jpg',
                1600,
                1200,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $service = app(
        MediaImageVariantService::class,
    );

    $service->generate(
        $media,
    );

    $oldPaths = $media
        ->variants()
        ->pluck('path')
        ->all();

    $service->generate(
        $media,
    );

    $newPaths = $media
        ->variants()
        ->pluck('path')
        ->all();

    expect($newPaths)
        ->not->toBe($oldPaths);

    foreach ($oldPaths as $oldPath) {
        expect($oldPath)
            ->toBeString();

        Storage::disk(
            'public',
        )->assertMissing(
            $oldPath,
        );
    }

    foreach ($newPaths as $newPath) {
        expect($newPath)
            ->toBeString();

        Storage::disk(
            'public',
        )->assertExists(
            $newPath,
        );
    }
});

test('documents cannot generate image variants', function (): void {
    $media = MediaAsset::factory()
        ->document()
        ->create();

    expect(
        fn () => app(
            MediaImageVariantService::class,
        )->generate(
            $media,
        ),
    )->toThrow(
        RuntimeException::class,
    );
});
