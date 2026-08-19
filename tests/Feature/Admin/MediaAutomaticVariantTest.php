<?php

use App\Enums\MediaType;
use App\Enums\MediaVariantPreset;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaMetadataService;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

test('image upload automatically generates image variants', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'automatic.jpg',
                1600,
                1200,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    expect(
        $media->variants()->count(),
    )->toBe(2);

    $thumbnail =
        $media->variant(
            MediaVariantPreset::Thumbnail,
        );

    $medium =
        $media->variant(
            MediaVariantPreset::Medium,
        );

    expect($thumbnail)
        ->not->toBeNull()

        ->and($medium)
        ->not->toBeNull();

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

test('document upload does not generate image variants', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->createWithContent(
                'document.pdf',
                "%PDF-1.4\n".
                "1 0 obj\n".
                "<< /Type /Catalog >>\n".
                "endobj\n".
                "%%EOF\n",
            ),

        type: MediaType::Document,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    expect(
        $media->variants()->count(),
    )->toBe(0);
});

test('image exceeding configured dimensions is rejected before storage', function (): void {
    config([
        'media.image_processing.max_width' => 300,
    ]);

    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'too-wide.jpg',
            640,
            480,
        );

    expect(
        fn () => app(
            MediaUploadService::class,
        )->upload(
            file: $file,
            type: MediaType::Image,
            visibility: MediaVisibility::Public,
            actor: $operator,
        ),
    )->toThrow(
        ValidationException::class,
    );

    expect(
        MediaAsset::query()->count(),
    )->toBe(0);

    expect(
        Storage::disk(
            'public',
        )->allFiles(),
    )->toBe([]);
});

test('image exceeding configured total pixels is rejected', function (): void {
    config([
        'media.image_processing.max_pixels' => 100000,
    ]);

    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'too-many-pixels.jpg',
            640,
            480,
        );

    expect(
        fn () => app(
            MediaUploadService::class,
        )->upload(
            file: $file,
            type: MediaType::Image,
            visibility: MediaVisibility::Public,
            actor: $operator,
        ),
    )->toThrow(
        ValidationException::class,
    );

    expect(
        MediaAsset::query()->count(),
    )->toBe(0);
});

test('changing public image to internal moves original and variants', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'move-private.jpg',
                800,
                600,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $paths = [
        (string) $media->path,
        ...$media
            ->variants()
            ->pluck('path')
            ->map(
                fn (mixed $path): string => (string) $path,
            )
            ->all(),
    ];

    app(
        MediaMetadataService::class,
    )->update(
        media: $media,
        actor: $operator,
        title: (string) $media->title,
        altText: $media->alt_text,
        caption: $media->caption,
        visibility: MediaVisibility::Internal,
    );

    $media->refresh();

    expect($media->disk)
        ->toBe('local')

        ->and($media->visibility)
        ->toBe(
            MediaVisibility::Internal,
        );

    foreach (
        $media->variants()->get() as $variant
    ) {
        expect($variant->disk)
            ->toBe('local');
    }

    foreach ($paths as $path) {
        Storage::disk(
            'local',
        )->assertExists(
            $path,
        );

        Storage::disk(
            'public',
        )->assertMissing(
            $path,
        );
    }
});

test('changing internal image to public moves original and variants', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'move-public.jpg',
                800,
                600,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Internal,

        actor: $operator,
    );

    $paths = [
        (string) $media->path,
        ...$media
            ->variants()
            ->pluck('path')
            ->map(
                fn (mixed $path): string => (string) $path,
            )
            ->all(),
    ];

    app(
        MediaMetadataService::class,
    )->update(
        media: $media,
        actor: $operator,
        title: (string) $media->title,
        altText: $media->alt_text,
        caption: $media->caption,
        visibility: MediaVisibility::Public,
    );

    $media->refresh();

    expect($media->disk)
        ->toBe('public')

        ->and($media->visibility)
        ->toBe(
            MediaVisibility::Public,
        );

    foreach (
        $media->variants()->get() as $variant
    ) {
        expect($variant->disk)
            ->toBe('public');
    }

    foreach ($paths as $path) {
        Storage::disk(
            'public',
        )->assertExists(
            $path,
        );

        Storage::disk(
            'local',
        )->assertMissing(
            $path,
        );
    }
});
