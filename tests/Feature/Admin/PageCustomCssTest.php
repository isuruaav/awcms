<?php

use App\Enums\PageEditorMode;
use App\Livewire\Admin\Pages\PageCreate;
use App\Livewire\Admin\Pages\PageEdit;
use App\Models\Page;
use App\Models\User;
use App\Services\PageRevisionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->editor = User::factory()->create();
    $this->editor->assignRole('Content Editor');
});

it('creates pages with custom CSS in each language', function (string $locale): void {
    $css = '.sos-course-card { color: #ffffff; background: #102b43; }';

    Livewire::actingAs($this->editor)->test(PageCreate::class)
        ->assertSee('Custom CSS')
        ->set('title', 'Courses')
        ->set('locale', $locale)
        ->set('editorMode', PageEditorMode::Html->value)
        ->set('content', '<article class="sos-course-card"><h3>Courses / පාඨමාලා</h3></article>')
        ->set('customCss', $css)
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::where('locale', $locale)->firstOrFail();

    expect($page->custom_css)->toBe($css)
        ->and($page->revisions()->firstOrFail()->custom_css)->toBe($css);
})->with(['en', 'si']);

it('detects CSS-only edits and can restore and remove styles', function (): void {
    $page = Page::factory()->create(['custom_css' => '.card { color: green; }']);
    $original = app(PageRevisionService::class)->capture($page, $this->editor, 'Original styles');

    Livewire::actingAs($this->editor)->test(PageEdit::class, ['page' => $page])
        ->assertSet('customCss', '.card { color: green; }')
        ->set('customCss', '.card { color: blue; }')
        ->call('save')->assertHasNoErrors();

    expect($page->refresh()->custom_css)->toBe('.card { color: blue; }')
        ->and($page->revisions()->count())->toBe(2);

    app(PageRevisionService::class)->restore($original, $this->editor);

    expect($page->refresh()->custom_css)->toBe('.card { color: green; }')
        ->and($page->revisions()->count())->toBe(3);

    Livewire::actingAs($this->editor)->test(PageEdit::class, ['page' => $page])
        ->set('customCss', '')
        ->call('save')->assertHasNoErrors();

    expect($page->refresh()->custom_css)->toBeNull()
        ->and($page->revisions()->firstOrFail()->custom_css)->toBeNull();
});

it('copies styling into a new translation without copying the wording', function (): void {
    $page = Page::factory()->create(['custom_css' => '.card { color: green; }']);

    Livewire::actingAs($this->editor)->test(PageCreate::class, ['pageId' => $page->id, 'locale' => 'si'])
        ->assertSet('content', '')
        ->assertSet('title', '')
        ->assertSet('customCss', '.card { color: green; }')
        ->set('title', 'පාඨමාලා')
        ->set('slug', 'courses')
        ->set('customCss', '.card { color: blue; }')
        ->call('save')->assertHasNoErrors();

    expect($page->refresh()->custom_css)->toBe('.card { color: green; }')
        ->and(Page::where('locale', 'si')->firstOrFail()->custom_css)->toBe('.card { color: blue; }');
});

it('shows a CSS validation error and leaves the existing draft unchanged', function (): void {
    $page = Page::factory()->create(['custom_css' => '.card { color: green; }']);

    Livewire::actingAs($this->editor)->test(PageEdit::class, ['page' => $page])
        ->set('customCss', '</style><script>alert(1)</script>')
        ->call('save')->assertHasErrors(['customCss']);

    expect($page->refresh()->custom_css)->toBe('.card { color: green; }');

    expect(fn () => $page->update(['custom_css' => '@import "https://example.com/style.css";']))
        ->toThrow(ValidationException::class);
});

it('shows escaped CSS source in revision history', function (): void {
    $page = Page::factory()->create(['custom_css' => '.card::before { content: "Hello & goodbye"; }']);
    app(PageRevisionService::class)->capture($page, $this->editor, 'Styles');

    $this->actingAs($this->editor)->get(route('admin.pages.revisions', $page))
        ->assertOk()->assertSee('Custom CSS Comparison')->assertSee('Hello &amp; goodbye', false);
});

it('renders page CSS in saved previews', function (): void {
    $page = Page::factory()->create([
        'editor_mode' => PageEditorMode::Visual->value,
        'content' => '<h3 class="card" style="color:#047857">පාඨමාලා</h3>',
        'custom_css' => '.card { background: #102b43; }',
    ]);

    $this->actingAs($this->editor)->get(route('admin.pages.preview', $page))
        ->assertOk()
        ->assertSee('id="cms-page-'.$page->id.'"', false)
        ->assertSee(':where(#cms-page-'.$page->id.') .card', false)
        ->assertSee('background: #102b43', false)
        ->assertSee('style="color:', false);
});
