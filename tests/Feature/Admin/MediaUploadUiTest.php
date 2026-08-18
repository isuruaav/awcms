<?php

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Media\MediaUpload;
use App\Models\MediaAsset;
use App\Models\User;
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

test('media operator can open media upload screen', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs($operator)
        ->get(
            route(
                'admin.media.upload',
            ),
        )
        ->assertOk()
        ->assertSee('Upload Media')
        ->assertSee('Select File')
        ->assertSee('Media Type')
        ->assertSee('Visibility')
        ->assertSee('Alternative Text');
});

test('auditor cannot access media upload screen', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $this->actingAs($auditor)
        ->get(
            route(
                'admin.media.upload',
            ),
        )
        ->assertForbidden();
});

test('media upload screen requires a file', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->call('save')
        ->assertHasErrors([
            'file',
        ]);
});

test('media operator can upload image through livewire screen', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'training-photo.jpg',
            800,
            600,
        )
        ->size(500);

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->set(
            'file',
            $file,
        )
        ->set(
            'type',
            MediaType::Image->value,
        )
        ->set(
            'visibility',
            MediaVisibility::Public->value,
        )
        ->set(
            'title',
            'Training Photo',
        )
        ->set(
            'altText',
            'Army training activity',
        )
        ->set(
            'caption',
            'Official training photograph.',
        )
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet(
            'file',
            null,
        );

    $media = MediaAsset::query()
        ->where(
            'title',
            'Training Photo',
        )
        ->firstOrFail();

    expect($media->type)
        ->toBe(MediaType::Image)

        ->and($media->visibility)
        ->toBe(MediaVisibility::Public)

        ->and($media->alt_text)
        ->toBe('Army training activity')

        ->and($media->caption)
        ->toBe(
            'Official training photograph.',
        );

    Storage::disk(
        'public',
    )->assertExists(
        (string) $media->path,
    );
});

test('media operator can upload pdf through livewire screen', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->createWithContent(
            'notice.pdf',
            "%PDF-1.4\n".
            "1 0 obj\n".
            "<< /Type /Catalog >>\n".
            "endobj\n".
            "%%EOF\n",
        );

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->set(
            'file',
            $file,
        )
        ->set(
            'type',
            MediaType::Document->value,
        )
        ->set(
            'visibility',
            MediaVisibility::Public->value,
        )
        ->set(
            'title',
            'Official Notice',
        )
        ->call('save')
        ->assertHasNoErrors();

    $media = MediaAsset::query()
        ->where(
            'title',
            'Official Notice',
        )
        ->firstOrFail();

    expect($media->type)
        ->toBe(MediaType::Document)

        ->and($media->mime_type)
        ->toBe('application/pdf')

        ->and($media->extension)
        ->toBe('pdf');
});

test('internal upload from screen uses private disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'internal.jpg',
            640,
            480,
        );

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->set(
            'file',
            $file,
        )
        ->set(
            'type',
            MediaType::Image->value,
        )
        ->set(
            'visibility',
            MediaVisibility::Internal->value,
        )
        ->call('save')
        ->assertHasNoErrors();

    $media = MediaAsset::query()
        ->latest('id')
        ->firstOrFail();

    expect($media->disk)
        ->toBe('local');

    Storage::disk(
        'local',
    )->assertExists(
        (string) $media->path,
    );
});

test('unsupported media type cannot be submitted through ui', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'photo.jpg',
        );

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->set(
            'file',
            $file,
        )
        ->set(
            'type',
            'video',
        )
        ->call('save')
        ->assertHasErrors([
            'type',
        ]);
});

test('successful upload resets form defaults', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'reset-test.jpg',
        );

    Livewire::actingAs($operator)
        ->test(MediaUpload::class)
        ->set(
            'file',
            $file,
        )
        ->set(
            'type',
            MediaType::Image->value,
        )
        ->set(
            'visibility',
            MediaVisibility::Internal->value,
        )
        ->set(
            'title',
            'Reset Test',
        )
        ->set(
            'altText',
            'Reset alt text',
        )
        ->set(
            'caption',
            'Reset caption',
        )
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet(
            'file',
            null,
        )
        ->assertSet(
            'title',
            '',
        )
        ->assertSet(
            'altText',
            '',
        )
        ->assertSet(
            'caption',
            '',
        )
        ->assertSet(
            'type',
            MediaType::Image->value,
        )
        ->assertSet(
            'visibility',
            MediaVisibility::Public->value,
        );
});
