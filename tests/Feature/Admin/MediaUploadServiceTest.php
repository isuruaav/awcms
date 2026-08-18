<?php

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaUploadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
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

test('media operator can securely upload public image', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'army-training.jpg',
            640,
            480,
        )
        ->size(500);

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Image,
        visibility: MediaVisibility::Public,
        actor: $operator,
        title: 'Army Training',
        altText: 'Training activity',
        caption: 'Official training activity.',
    );

    expect($media)
        ->toBeInstanceOf(MediaAsset::class)

        ->and($media->title)
        ->toBe('Army Training')

        ->and($media->mime_type)
        ->toBe('image/jpeg')

        ->and($media->extension)
        ->toBe('jpg')

        ->and($media->width)
        ->toBe(640)

        ->and($media->height)
        ->toBe(480)

        ->and($media->disk)
        ->toBe('public')

        ->and($media->uploaded_by)
        ->toBe($operator->id);

    expect($media->stored_name)
        ->toMatch(
            '/^[0-9a-f-]{36}\.jpg$/',
        )
        ->not->toBe(
            'army-training.jpg',
        );

    Storage::disk(
        'public',
    )->assertExists(
        (string) $media->path,
    );
});

test('uploaded image receives sha256 checksum', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'checksum-image.png',
            320,
            240,
        );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Image,
        visibility: MediaVisibility::Public,
        actor: $operator,
    );

    $path = Storage::disk(
        'public',
    )->path(
        (string) $media->path,
    );

    expect($media->checksum)
        ->toBe(
            hash_file(
                'sha256',
                $path,
            ),
        );
});

test('pdf document can be securely uploaded', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->createWithContent(
            'official-document.pdf',
            "%PDF-1.4\n".
                "1 0 obj\n".
                "<< /Type /Catalog >>\n".
                "endobj\n".
                "%%EOF\n",
        );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Document,
        visibility: MediaVisibility::Public,
        actor: $operator,
        title: 'Official Document',
    );

    expect($media->mime_type)
        ->toBe('application/pdf')

        ->and($media->extension)
        ->toBe('pdf')

        ->and($media->width)
        ->toBeNull()

        ->and($media->height)
        ->toBeNull();

    Storage::disk(
        'public',
    )->assertExists(
        (string) $media->path,
    );
});

test('internal media is stored on private disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'internal-photo.jpg',
            640,
            480,
        );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Image,
        visibility: MediaVisibility::Internal,
        actor: $operator,
    );

    expect($media->disk)
        ->toBe('local');

    Storage::disk(
        'local',
    )->assertExists(
        (string) $media->path,
    );

    Storage::disk(
        'public',
    )->assertMissing(
        (string) $media->path,
    );
});

test('restricted media is stored on private disk', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'restricted.jpg',
            640,
            480,
        );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Image,
        visibility: MediaVisibility::Restricted,
        actor: $operator,
    );

    expect($media->disk)
        ->toBe('local');

    Storage::disk(
        'local',
    )->assertExists(
        (string) $media->path,
    );
});

test('svg upload is rejected', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->createWithContent(
            'unsafe.svg',
            '<svg xmlns="http://www.w3.org/2000/svg">'
                .'<script>alert(1)</script>'
                .'</svg>',
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
        Storage::disk(
            'public',
        )->allFiles(),
    )->toBe([]);
});

test('file with mismatched extension and content is rejected', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    /*
     * PDF bytes disguised using a JPG filename.
     */
    $file = UploadedFile::fake()
        ->createWithContent(
            'disguised.jpg',
            "%PDF-1.4\n".
                "1 0 obj\n".
                "<< /Type /Catalog >>\n".
                "endobj\n".
                "%%EOF\n",
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
});

test('oversized media file is rejected', function (): void {
    config([
        'media.uploads.image.max_kb' => 1,
    ]);

    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'too-large.jpg',
            640,
            480,
        )
        ->size(50);

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

test('user without upload permission cannot upload media', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $file = UploadedFile::fake()
        ->image(
            'not-authorized.jpg',
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
            actor: $auditor,
        ),
    )->toThrow(
        AuthorizationException::class,
    );

    expect(
        MediaAsset::query()->count(),
    )->toBe(0);
});

test('media upload is recorded in audit log', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $file = UploadedFile::fake()
        ->image(
            'audit-image.jpg',
            640,
            480,
        );

    $media = app(
        MediaUploadService::class,
    )->upload(
        file: $file,
        type: MediaType::Image,
        visibility: MediaVisibility::Public,
        actor: $operator,
    );

    $log = AuditLog::query()
        ->where(
            'event',
            'media.uploaded',
        )
        ->latest('id')
        ->firstOrFail();

    expect($log->actor_id)
        ->toBe($operator->id)

        ->and($log->subject_id)
        ->toBe($media->id)

        ->and($log->new_values)
        ->toMatchArray([
            'type' => MediaType::Image->value,

            'visibility' => MediaVisibility::Public->value,

            'mime_type' => 'image/jpeg',

            'extension' => 'jpg',
        ]);
});
