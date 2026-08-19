<?php

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaReplacementService;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

/**
 * Create a real-content PDF upload.
 *
 * MediaUploadService detects MIME using finfo,
 * therefore an empty fake file is not sufficient.
 */
function awcmsReplacementPdf(
    string $name = 'document.pdf',
): UploadedFile {
    $content = <<<'PDF'
%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Count 0 >>
endobj
trailer
<< /Root 1 0 R >>
%%EOF
PDF;

    return UploadedFile::fake()
        ->createWithContent(
            $name,
            $content,
        );
}

test('media operator can replace an image while preserving media identity and metadata', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'original.jpg',
            800,
            600,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,

        title: 'Website Hero Image',

        altText: 'Army website hero image',

        caption: 'Original hero image caption',
    );

    $media->load(
        'variants',
    );

    $oldId = $media->id;

    $oldUuid = $media->uuid;

    $oldTitle = $media->title;

    $oldAltText = $media->alt_text;

    $oldCaption = $media->caption;

    $oldVisibility =
        $media->visibility;

    $oldUploadedBy =
        $media->uploaded_by;

    $oldChecksum =
        $media->checksum;

    $oldOriginalPath =
        (string) $media->path;

    $oldVariantPaths =
        $media->variants
            ->pluck('path')
            ->filter(
                fn (mixed $path): bool => is_string($path)
                    && trim($path) !== '',
            )
            ->values()
            ->all();

    Storage::disk(
        'public',
    )->assertExists(
        $oldOriginalPath,
    );

    foreach (
        $oldVariantPaths as $oldVariantPath
    ) {
        Storage::disk(
            'public',
        )->assertExists(
            $oldVariantPath,
        );
    }

    $replacement =
        UploadedFile::fake()->image(
            'replacement.png',
            1200,
            900,
        );

    $replaced = app(
        MediaReplacementService::class,
    )->replace(
        media: $media,

        file: $replacement,

        actor: $operator,
    );

    $replaced->load(
        'variants',
    );

    expect(
        $replaced->id,
    )->toBe(
        $oldId,
    );

    expect(
        $replaced->uuid,
    )->toBe(
        $oldUuid,
    );

    expect(
        $replaced->title,
    )->toBe(
        $oldTitle,
    );

    expect(
        $replaced->alt_text,
    )->toBe(
        $oldAltText,
    );

    expect(
        $replaced->caption,
    )->toBe(
        $oldCaption,
    );

    expect(
        $replaced->visibility,
    )->toBe(
        $oldVisibility,
    );

    expect(
        $replaced->uploaded_by,
    )->toBe(
        $oldUploadedBy,
    );

    expect(
        $replaced->original_name,
    )->toBe(
        'replacement.png',
    );

    expect(
        $replaced->mime_type,
    )->toBe(
        'image/png',
    );

    expect(
        $replaced->extension,
    )->toBe(
        'png',
    );

    expect(
        $replaced->width,
    )->toBe(
        1200,
    );

    expect(
        $replaced->height,
    )->toBe(
        900,
    );

    expect(
        $replaced->checksum,
    )->not->toBe(
        $oldChecksum,
    );

    expect(
        $replaced->variants,
    )->toHaveCount(
        2,
    );

    $newOriginalPath =
        (string) $replaced->path;

    expect(
        $newOriginalPath,
    )->not->toBe(
        $oldOriginalPath,
    );

    Storage::disk(
        'public',
    )->assertExists(
        $newOriginalPath,
    );

    /*
     * Old original must disappear only after the
     * replacement operation has committed.
     */
    Storage::disk(
        'public',
    )->assertMissing(
        $oldOriginalPath,
    );

    /*
     * Old image variants must also be removed.
     */
    foreach (
        $oldVariantPaths as $oldVariantPath
    ) {
        Storage::disk(
            'public',
        )->assertMissing(
            $oldVariantPath,
        );
    }

    /*
     * New variants must exist physically.
     */
    foreach (
        $replaced->variants as $variant
    ) {
        $variantPath =
            $variant->getAttribute(
                'path',
            );

        expect(
            $variantPath,
        )->toBeString();

        Storage::disk(
            'public',
        )->assertExists(
            (string) $variantPath,
        );
    }
});

test('media operator can replace a pdf document without changing media identity', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: awcmsReplacementPdf(
            'original.pdf',
        ),

        type: MediaType::Document,

        visibility: MediaVisibility::Public,

        actor: $operator,

        title: 'Official Document',

        caption: 'Official PDF document',
    );

    $oldId =
        $media->id;

    $oldUuid =
        $media->uuid;

    $oldTitle =
        $media->title;

    $oldCaption =
        $media->caption;

    $oldOriginalPath =
        (string) $media->path;

    Storage::disk(
        'public',
    )->assertExists(
        $oldOriginalPath,
    );

    $replaced = app(
        MediaReplacementService::class,
    )->replace(
        media: $media,

        file: awcmsReplacementPdf(
            'replacement.pdf',
        ),

        actor: $operator,
    );

    $replaced->load(
        'variants',
    );

    expect(
        $replaced->id,
    )->toBe(
        $oldId,
    );

    expect(
        $replaced->uuid,
    )->toBe(
        $oldUuid,
    );

    expect(
        $replaced->title,
    )->toBe(
        $oldTitle,
    );

    expect(
        $replaced->caption,
    )->toBe(
        $oldCaption,
    );

    expect(
        $replaced->original_name,
    )->toBe(
        'replacement.pdf',
    );

    expect(
        $replaced->mime_type,
    )->toBe(
        'application/pdf',
    );

    expect(
        $replaced->extension,
    )->toBe(
        'pdf',
    );

    expect(
        $replaced->width,
    )->toBeNull();

    expect(
        $replaced->height,
    )->toBeNull();

    expect(
        $replaced->variants,
    )->toHaveCount(
        0,
    );

    Storage::disk(
        'public',
    )->assertMissing(
        $oldOriginalPath,
    );

    Storage::disk(
        'public',
    )->assertExists(
        (string) $replaced->path,
    );
});

test('image media cannot be replaced with a pdf document', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'original.jpg',
            640,
            480,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,

        title: 'Protected Image',
    );

    $oldPath =
        (string) $media->path;

    $oldChecksum =
        $media->checksum;

    expect(
        fn () => app(
            MediaReplacementService::class,
        )->replace(
            media: $media,

            file: awcmsReplacementPdf(
                'wrong-type.pdf',
            ),

            actor: $operator,
        ),
    )->toThrow(
        ValidationException::class,
    );

    $media->refresh();

    expect(
        $media->path,
    )->toBe(
        $oldPath,
    );

    expect(
        $media->checksum,
    )->toBe(
        $oldChecksum,
    );

    Storage::disk(
        'public',
    )->assertExists(
        $oldPath,
    );
});

test('auditor cannot replace media files', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'original.jpg',
            640,
            480,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $oldPath =
        (string) $media->path;

    expect(
        fn () => app(
            MediaReplacementService::class,
        )->replace(
            media: $media,

            file: UploadedFile::fake()
                ->image(
                    'replacement.jpg',
                    800,
                    600,
                ),

            actor: $auditor,
        ),
    )->toThrow(
        AuthorizationException::class,
    );

    $media->refresh();

    expect(
        $media->path,
    )->toBe(
        $oldPath,
    );

    Storage::disk(
        'public',
    )->assertExists(
        $oldPath,
    );
});

test('trashed media cannot be replaced', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'original.jpg',
            640,
            480,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $oldPath =
        (string) $media->path;

    $media->delete();

    expect(
        $media->trashed(),
    )->toBeTrue();

    expect(
        fn () => app(
            MediaReplacementService::class,
        )->replace(
            media: $media,

            file: UploadedFile::fake()
                ->image(
                    'replacement.jpg',
                    800,
                    600,
                ),

            actor: $operator,
        ),
    )->toThrow(
        RuntimeException::class,
        'A deleted media asset cannot be replaced.',
    );

    Storage::disk(
        'public',
    )->assertExists(
        $oldPath,
    );
});

test('external media cannot be replaced with an uploaded file', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'type' => MediaType::Image->value,

        'source' => MediaSource::External->value,

        'visibility' => MediaVisibility::Public->value,

        'external_url' => 'https://example.test/media/image.jpg',

        'disk' => null,

        'directory' => null,

        'stored_name' => null,

        'path' => null,
    ]);

    expect(
        fn () => app(
            MediaReplacementService::class,
        )->replace(
            media: $media,

            file: UploadedFile::fake()
                ->image(
                    'replacement.jpg',
                    800,
                    600,
                ),

            actor: $operator,
        ),
    )->toThrow(
        RuntimeException::class,
        'Only uploaded media files can be replaced.',
    );

    $media->refresh();

    expect(
        $media->source,
    )->toBe(
        MediaSource::External,
    );

    expect(
        $media->external_url,
    )->toBe(
        'https://example.test/media/image.jpg',
    );
});

test('internal media replacement remains on the private storage disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'internal-original.jpg',
            640,
            480,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Internal,

        actor: $operator,

        title: 'Internal Image',
    );

    expect(
        $media->disk,
    )->toBe(
        'local',
    );

    $oldPath =
        (string) $media->path;

    Storage::disk(
        'local',
    )->assertExists(
        $oldPath,
    );

    $replaced = app(
        MediaReplacementService::class,
    )->replace(
        media: $media,

        file: UploadedFile::fake()
            ->image(
                'internal-replacement.png',
                900,
                700,
            ),

        actor: $operator,
    );

    $replaced->load(
        'variants',
    );

    expect(
        $replaced->disk,
    )->toBe(
        'local',
    );

    expect(
        $replaced->visibility,
    )->toBe(
        MediaVisibility::Internal,
    );

    Storage::disk(
        'local',
    )->assertMissing(
        $oldPath,
    );

    Storage::disk(
        'local',
    )->assertExists(
        (string) $replaced->path,
    );

    foreach (
        $replaced->variants as $variant
    ) {
        expect(
            $variant->disk,
        )->toBe(
            'local',
        );

        Storage::disk(
            'local',
        )->assertExists(
            (string) $variant->path,
        );
    }
});
