<?php

use App\Livewire\Admin\Publications\PublicationUploads;
use App\Models\Publication;
use App\Models\User;
use App\Services\PublicationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function publicationTestPdf(string $name = 'journal.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
}

beforeEach(function (): void {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->administrator = User::factory()->create();
    $this->administrator->assignRole('Site Administrator');
    $this->actingAs($this->administrator);
});

it('uploads a publication and cover privately and publishes it from the admin panel', function (): void {
    Livewire::test(PublicationUploads::class)->call('create')->set('title', 'Signals Journal')
        ->set('pdf', publicationTestPdf())->set('cover', UploadedFile::fake()->image('cover.jpg', 600, 800))
        ->call('save')->assertHasNoErrors()->assertSet('formOpen', false);
    $record = Publication::query()->sole();
    expect($record->is_published)->toBeTrue()->and($record->disk)->toBe('local');
    Storage::disk('local')->assertExists([$record->pdf_path, $record->cover_path]);
    $this->assertDatabaseHas('audit_logs', ['event' => 'publications.created', 'subject_id' => $record->id]);
    $this->get(route('admin.publications.index'))->assertOk()->assertSeeText('Signals Journal');
});

it('saves Sinhala publications as separate localized PDF records', function (): void {
    Livewire::test(PublicationUploads::class)->call('create')->set('locale', 'si')->set('title', 'සිංහල සඟරාව')
        ->set('pdf', publicationTestPdf('sinhala-journal.pdf'))
        ->set('cover', UploadedFile::fake()->image('sinhala-cover.jpg', 600, 800))
        ->call('save')->assertHasNoErrors();

    $record = Publication::query()->sole();
    expect($record->locale)->toBe('si')->and($record->title)->toBe('සිංහල සඟරාව');
    $this->get('/publications')->assertOk()->assertDontSeeText('සිංහල සඟරාව');
    $this->get('/si/publications')->assertOk()->assertSeeText('සිංහල සඟරාව');
});

it('does not save a PDF until its cover upload is complete', function (): void {
    Livewire::test(PublicationUploads::class)->call('create')->set('title', 'Incomplete upload')
        ->set('pdf', publicationTestPdf())->call('save')->assertHasErrors(['cover' => 'required']);
    expect(Publication::query()->count())->toBe(0);
});

it('rejects a non PDF file disguised with a PDF extension', function (): void {
    Livewire::test(PublicationUploads::class)->call('create')->set('title', 'Invalid file')
        ->set('pdf', UploadedFile::fake()->createWithContent('fake.pdf', '<html>not a PDF</html>'))
        ->set('cover', UploadedFile::fake()->image('cover.jpg', 600, 800))
        ->call('save')->assertHasErrors(['pdf']);
    expect(Publication::query()->count())->toBe(0);
});

it('lets a content editor save drafts but prevents publishing through a tampered flag', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');
    $this->actingAs($editor);
    Livewire::test(PublicationUploads::class)->call('create')->assertSet('isPublished', false)->set('title', 'Editor draft')
        ->set('pdf', publicationTestPdf())->set('cover', UploadedFile::fake()->image('cover.jpg', 600, 800))
        ->call('save')->assertHasNoErrors();
    expect(Publication::query()->sole()->is_published)->toBeFalse();
    Livewire::test(PublicationUploads::class)->call('create')->set('title', 'Unauthorized publication')->set('isPublished', true)
        ->set('pdf', publicationTestPdf())->set('cover', UploadedFile::fake()->image('cover.jpg', 600, 800))
        ->call('save')->assertForbidden();
    expect(Publication::query()->count())->toBe(1);
});

it('keeps the current PDF for title edits and replaces both PDF and cover together', function (): void {
    $service = app(PublicationService::class);
    $record = $service->save($this->administrator, 'Original title', '', true, publicationTestPdf(), UploadedFile::fake()->image('cover.jpg', 600, 800));
    $oldPdf = $record->pdf_path;
    $oldCover = $record->cover_path;
    Livewire::test(PublicationUploads::class)->call('edit', $record->id)->set('title', 'Updated title')->call('save')->assertHasNoErrors();
    expect($record->fresh()->pdf_path)->toBe($oldPdf);
    Livewire::test(PublicationUploads::class)->call('edit', $record->id)->set('pdf', publicationTestPdf('replacement.pdf'))
        ->set('cover', UploadedFile::fake()->image('replacement.jpg', 500, 700))->call('save')->assertHasNoErrors();
    $record->refresh();
    expect($record->pdf_path)->not->toBe($oldPdf)->and($record->cover_width)->toBe(500);
    Storage::disk('local')->assertMissing([$oldPdf, $oldCover]);
    Storage::disk('local')->assertExists([$record->pdf_path, $record->cover_path]);
});

it('allows replacing a publication cover image without replacing its PDF', function (): void {
    $service = app(PublicationService::class);
    $record = $service->save($this->administrator, 'Signals Journal', '', true, publicationTestPdf(), UploadedFile::fake()->image('cover.jpg', 600, 800));
    $oldPdf = $record->pdf_path;
    $oldCover = $record->cover_path;

    $updated = $service->save(
        actor: $this->administrator,
        title: 'Signals Journal',
        titleSi: '',
        publish: true,
        pdf: null,
        cover: UploadedFile::fake()->image('new-cover.jpg', 500, 700),
        publication: $record,
    );

    expect($updated->pdf_path)->toBe($oldPdf)->and($updated->cover_path)->not->toBe($oldCover)
        ->and($updated->cover_width)->toBe(500);
    Storage::disk('local')->assertExists($oldPdf);
    Storage::disk('local')->assertMissing($oldCover);
});

it('unpublishes and deletes publications without exposing their private files', function (): void {
    $record = app(PublicationService::class)->save($this->administrator, 'Publication', '', true, publicationTestPdf(), UploadedFile::fake()->image('cover.jpg', 600, 800));
    Livewire::test(PublicationUploads::class)->call('unpublish', $record->id);
    $this->get(route('publications.pdf', ['publication' => $record->uuid]))->assertNotFound();
    Livewire::test(PublicationUploads::class)->call('publish', $record->id)->call('delete', $record->id);
    $this->assertSoftDeleted('publications', ['id' => $record->id]);
    $this->get(route('publications.cover', ['publication' => $record->uuid]))->assertNotFound();
});

it('removes newly stored files when saving the database record fails', function (): void {
    Event::listen('eloquent.created: '.Publication::class, function (): void {
        throw new RuntimeException('Simulated database failure');
    });
    try {
        expect(fn () => app(PublicationService::class)->save($this->administrator, 'Rollback', '', true, publicationTestPdf(), UploadedFile::fake()->image('cover.jpg', 600, 800)))
            ->toThrow(RuntimeException::class, 'Simulated database failure');
    } finally {
        Event::forget('eloquent.created: '.Publication::class);
    }
    expect(Publication::query()->count())->toBe(0)->and(Storage::disk('local')->allFiles('publications'))->toBe([]);
});

it('denies publication management to an account without document permissions', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('admin.access');
    $this->actingAs($user)->get(route('admin.publications.index'))->assertForbidden();
});
