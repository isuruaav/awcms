<?php

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Media\MediaEdit;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

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

test('media operator can see the replace file interface', function (): void {
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

        title: 'Replace UI Image',
    );

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->assertSet(
            'canReplace',
            true,
        )
        ->assertSee(
            'Replace File',
        )
        ->assertSee(
            'original.jpg',
        )
        ->assertSee(
            'Existing file will be replaced',
        );
});

test('media operator can replace an image through media edit', function (): void {
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

        title: 'Livewire Replacement Image',

        altText: 'Original alternative text',

        caption: 'Original caption',
    );

    $media->load(
        'variants',
    );

    $mediaId =
        $media->id;

    $mediaUuid =
        $media->uuid;

    $oldPath =
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
        $oldPath,
    );

    $replacement =
        UploadedFile::fake()->image(
            'replacement.png',
            1200,
            900,
        );

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'replacementFile',
            $replacement,
        )
        ->call(
            'replaceFile',
        )
        ->assertHasNoErrors(
            'replacementFile',
        )
        ->assertSet(
            'replacementFile',
            null,
        )
        ->assertSee(
            'Media file was replaced successfully.',
        )
        ->assertSee(
            'replacement.png',
        );

    $media->refresh();

    $media->load(
        'variants',
    );

    expect(
        $media->id,
    )->toBe(
        $mediaId,
    );

    expect(
        $media->uuid,
    )->toBe(
        $mediaUuid,
    );

    expect(
        $media->title,
    )->toBe(
        'Livewire Replacement Image',
    );

    expect(
        $media->alt_text,
    )->toBe(
        'Original alternative text',
    );

    expect(
        $media->caption,
    )->toBe(
        'Original caption',
    );

    expect(
        $media->original_name,
    )->toBe(
        'replacement.png',
    );

    expect(
        $media->mime_type,
    )->toBe(
        'image/png',
    );

    expect(
        $media->width,
    )->toBe(
        1200,
    );

    expect(
        $media->height,
    )->toBe(
        900,
    );

    expect(
        $media->variants,
    )->toHaveCount(
        2,
    );

    Storage::disk(
        'public',
    )->assertMissing(
        $oldPath,
    );

    Storage::disk(
        'public',
    )->assertExists(
        (string) $media->path,
    );

    foreach (
        $oldVariantPaths as $oldVariantPath
    ) {
        Storage::disk(
            'public',
        )->assertMissing(
            $oldVariantPath,
        );
    }

    foreach (
        $media->variants as $variant
    ) {
        Storage::disk(
            'public',
        )->assertExists(
            (string) $variant->path,
        );
    }
});

test('media edit rejects replacement with the wrong physical media type', function (): void {
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

    $pdf = UploadedFile::fake()
        ->createWithContent(
            'replacement.pdf',
            <<<'PDF'
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
PDF,
        );

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'replacementFile',
            $pdf,
        )
        ->call(
            'replaceFile',
        )
        ->assertHasErrors(
            'replacementFile',
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

test('auditor cannot see or execute media replacement', function (): void {
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

    Livewire::actingAs(
        $auditor,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->assertSet(
            'canReplace',
            false,
        )
        ->assertDontSee(
            'Existing file will be replaced',
        );

    Livewire::actingAs(
        $auditor,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'replacementFile',
            UploadedFile::fake()->image(
                'replacement.jpg',
                800,
                600,
            ),
        )
        ->call(
            'replaceFile',
        )
        ->assertForbidden();

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

test('external media does not expose the replace file interface', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'type' => MediaType::Image->value,

        'source' => MediaSource::External->value,

        'visibility' => MediaVisibility::Public->value,

        'external_url' => 'https://example.test/image.jpg',

        'disk' => null,

        'directory' => null,

        'stored_name' => null,

        'path' => null,
    ]);

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->assertSet(
            'canReplace',
            false,
        )
        ->assertDontSee(
            'Existing file will be replaced',
        );
});
