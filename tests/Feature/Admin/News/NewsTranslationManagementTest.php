<?php

use App\Enums\NewsLocale;
use App\Livewire\Admin\News\NewsCreate;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('a news translation starts empty and must be entered manually', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $category = NewsCategory::factory()->create();

    $english = News::factory()->create([
        'category_id' => $category->id,
        'title' => 'Training Programme Begins',
        'slug' => 'training-programme-begins',
        'locale' => NewsLocale::English->value,
        'summary' => 'Approved English summary.',
        'content' => '<p>Approved English news wording.</p>',
    ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    Livewire::actingAs($editor)
        ->test(NewsCreate::class, [
            'newsId' => $english->id,
            'locale' => NewsLocale::Sinhala->value,
        ])
        ->assertSet(
            'locale',
            NewsLocale::Sinhala->value,
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
            'summary',
            '',
        )
        ->assertSet(
            'content',
            '',
        )
        ->set(
            'title',
            'පුහුණු වැඩසටහන ආරම්භ වේ',
        )
        ->set(
            'slug',
            'training-programme-begins',
        )
        ->set(
            'summary',
            'අනුමත සිංහල සාරාංශය.',
        )
        ->set(
            'content',
            '<p>අනුමත සිංහල පුවත් අන්තර්ගතය.</p>',
        )
        ->call('save')
        ->assertHasNoErrors();

    $sinhala = News::query()
        ->where(
            'translation_group',
            $translationGroup,
        )
        ->where(
            'locale',
            NewsLocale::Sinhala->value,
        )
        ->firstOrFail();

    expect($sinhala->title)
        ->toBe('පුහුණු වැඩසටහන ආරම්භ වේ')
        ->and($sinhala->slug)
        ->toBe('training-programme-begins')
        ->and($sinhala->content)
        ->toContain('අනුමත සිංහල පුවත් අන්තර්ගතය.')
        ->not->toContain('Approved English news wording.');
});

test('one news translation group cannot contain the same language twice', function (): void {
    $english = News::factory()->create([
        'locale' => NewsLocale::English->value,
    ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    News::factory()->create([
        'locale' => NewsLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]);

    expect(fn (): News => News::factory()->create([
        'locale' => NewsLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]))->toThrow(
        QueryException::class,
    );
});

test('the same news slug may be used once in each language', function (): void {
    $english = News::factory()->create([
        'slug' => 'common-public-slug',
        'locale' => NewsLocale::English->value,
    ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    $sinhala = News::factory()->create([
        'slug' => 'common-public-slug',
        'locale' => NewsLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]);

    $tamil = News::factory()->create([
        'slug' => 'common-public-slug',
        'locale' => NewsLocale::Tamil->value,
        'translation_group' => $translationGroup,
    ]);

    expect($english->slug)
        ->toBe('common-public-slug')
        ->and($sinhala->slug)
        ->toBe('common-public-slug')
        ->and($tamil->slug)
        ->toBe('common-public-slug');
});
