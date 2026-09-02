<?php

use App\Enums\PageLocale;
use App\Livewire\Admin\Pages\PageCreate;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('a translation starts empty and must be entered manually', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $english = Page::factory()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'locale' => PageLocale::English->value,
        'content' => '<p>Approved English wording.</p>',
    ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class, [
            'pageId' => $english->id,
            'locale' => PageLocale::Sinhala->value,
        ])
        ->assertSet(
            'locale',
            PageLocale::Sinhala->value,
        )
        ->assertSet(
            'translationGroup',
            $translationGroup,
        )
        ->assertSet(
            'title',
            '',
        )
        ->assertSet(
            'content',
            '',
        )
        ->set(
            'title',
            'අප ගැන',
        )
        ->set(
            'slug',
            'about-us',
        )
        ->set(
            'content',
            '<p>අපගේ නිල වෙබ් අඩවියට සාදරයෙන් පිළිගනිමු.</p>',
        )
        ->call('save')
        ->assertHasNoErrors();

    $sinhala = Page::query()
        ->where(
            'translation_group',
            $translationGroup,
        )
        ->where(
            'locale',
            PageLocale::Sinhala->value,
        )
        ->firstOrFail();

    expect($sinhala->title)
        ->toBe('අප ගැන')
        ->and($sinhala->slug)
        ->toBe('about-us')
        ->and($sinhala->content)
        ->toContain('අපගේ නිල වෙබ් අඩවියට')
        ->not->toContain('Approved English wording.');
});

test('one translation group cannot contain the same language twice', function (): void {
    $english = Page::factory()->create([
        'locale' => PageLocale::English->value,
    ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    Page::factory()->create([
        'locale' => PageLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]);

    expect(fn (): Page => Page::factory()->create([
        'locale' => PageLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]))->toThrow(
        QueryException::class,
    );
});
