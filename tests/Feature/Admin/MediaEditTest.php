<?php

use App\Enums\MediaType;
use App\Enums\MediaVariantPreset;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Media\MediaEdit;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaMetadataService;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
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

test('media operator can open media details screen', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Training Photograph',
    ]);

    $this->actingAs($operator)
        ->get(
            route(
                'admin.media.edit',
                $media,
            ),
        )
        ->assertOk()
        ->assertSee('Media Details')
        ->assertSee('Training Photograph')
        ->assertSee('File Information')
        ->assertSee('Save Changes');
});

test('public image details use medium variant preview', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()
            ->image(
                'preview.jpg',
                1600,
                1200,
            ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $operator,
    );

    $medium = $media->variant(
        MediaVariantPreset::Medium,
    );

    expect($medium)
        ->not->toBeNull();

    $mediumPath = (string) $medium?->getAttribute(
        'path',
    );

    expect($mediumPath)
        ->not->toBe('');

    $expectedUrl = Storage::disk(
        'public',
    )->url(
        $mediumPath,
    );

    $this->actingAs(
        $operator,
    )
        ->get(
            route(
                'admin.media.edit',
                $media,
            ),
        )
        ->assertOk()
        ->assertSee(
            $expectedUrl,
            false,
        );
});

test('auditor can view media details in read only mode', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Read Only Photograph',
    ]);

    $this->actingAs($auditor)
        ->get(
            route(
                'admin.media.edit',
                $media,
            ),
        )
        ->assertOk()
        ->assertSee('Read-only access')
        ->assertSee('Read Only Photograph')
        ->assertDontSee('Save Changes');
});

test('media operator can update image metadata', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Old Title',

        'alt_text' => 'Old alt',

        'caption' => 'Old caption',
    ]);

    Livewire::actingAs($operator)
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'title',
            'Updated Training Image',
        )
        ->set(
            'altText',
            'Personnel participating in training',
        )
        ->set(
            'caption',
            'Updated official photograph.',
        )
        ->call('save')
        ->assertHasNoErrors();

    $media->refresh();

    expect($media->title)
        ->toBe(
            'Updated Training Image',
        )

        ->and($media->alt_text)
        ->toBe(
            'Personnel participating in training',
        )

        ->and($media->caption)
        ->toBe(
            'Updated official photograph.',
        );
});

test('document metadata update clears image alternative text', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()
        ->document()
        ->create([
            'alt_text' => 'Should be removed',
        ]);

    Livewire::actingAs($operator)
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'title',
            'Updated Document',
        )
        ->set(
            'altText',
            'Not applicable',
        )
        ->call('save')
        ->assertHasNoErrors();

    expect(
        $media->refresh()->alt_text,
    )->toBeNull();
});

test('changing public media to internal moves physical file to private disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $path =
        'media/image/2026/08/'
        .'visibility-test.jpg';

    Storage::disk(
        'public',
    )->put(
        $path,
        'test-image-content',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Visibility Test',

        'type' => MediaType::Image->value,

        'visibility' => MediaVisibility::Public->value,

        'disk' => 'public',

        'path' => $path,

        'stored_name' => 'visibility-test.jpg',
    ]);

    Livewire::actingAs($operator)
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'visibility',
            MediaVisibility::Internal->value,
        )
        ->call('save')
        ->assertHasNoErrors();

    $media->refresh();

    expect($media->visibility)
        ->toBe(
            MediaVisibility::Internal,
        )

        ->and($media->disk)
        ->toBe('local');

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
});

test('changing internal media to public moves physical file to public disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $path =
        'media/image/2026/08/'
        .'publish-test.jpg';

    Storage::disk(
        'local',
    )->put(
        $path,
        'private-test-content',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Publish Test',

        'visibility' => MediaVisibility::Internal->value,

        'disk' => 'local',

        'path' => $path,

        'stored_name' => 'publish-test.jpg',
    ]);

    Livewire::actingAs($operator)
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'visibility',
            MediaVisibility::Public->value,
        )
        ->call('save')
        ->assertHasNoErrors();

    $media->refresh();

    expect($media->visibility)
        ->toBe(
            MediaVisibility::Public,
        )

        ->and($media->disk)
        ->toBe('public');

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
});

test('media update writes audit log without full caption content', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Audit Media',
    ]);

    Livewire::actingAs($operator)
        ->test(
            MediaEdit::class,
            [
                'media' => $media,
            ],
        )
        ->set(
            'title',
            'Audit Media Updated',
        )
        ->set(
            'caption',
            'Sensitive descriptive content.',
        )
        ->call('save')
        ->assertHasNoErrors();

    $log = AuditLog::query()
        ->where(
            'event',
            'media.updated',
        )
        ->latest('id')
        ->firstOrFail();

    expect($log->actor_id)
        ->toBe($operator->id)

        ->and($log->subject_id)
        ->toBe($media->id)

        ->and($log->new_values)
        ->toHaveKey(
            'caption_length',
        );

    expect(
        json_encode(
            $log->new_values,
        ),
    )->not->toContain(
        'Sensitive descriptive content.',
    );
});

test('auditor cannot update media metadata', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $media = MediaAsset::factory()->create();

    expect(
        fn () => app(
            MediaMetadataService::class,
        )->update(
            media: $media,
            actor: $auditor,
            title: 'Unauthorized Change',
            altText: null,
            caption: null,
            visibility: MediaVisibility::Public,
        ),
    )->toThrow(
        AuthorizationException::class,
    );
});
