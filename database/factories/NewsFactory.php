<?php

namespace Database\Factories;

use App\Enums\NewsEditorMode;
use App\Enums\NewsLocale;
use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
final class NewsFactory extends Factory
{
    protected $model = News::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()
            ->unique()
            ->sentence(
                6,
            );

        $paragraphs = fake()
            ->paragraphs(
                3,
            );

        $contentText = is_array(
            $paragraphs,
        )
            ? implode(
                "\n\n",
                $paragraphs,
            )
            : $paragraphs;

        return [
            'uuid' => Str::uuid()
                ->toString(),

            'locale' => NewsLocale::English->value,

            'translation_group' => Str::uuid()->toString(),

            'category_id' => NewsCategory::factory(),

            'title' => $title,

            'slug' => Str::slug(
                $title,
            ).'-'.fake()
                ->unique()
                ->numberBetween(
                    1000,
                    999999,
                ),

            'summary' => fake()->paragraph(),

            'content' => '<p>'
                .e(
                    $contentText,
                )
                .'</p>',

            'editor_mode' => NewsEditorMode::Visual->value,

            'featured_image_id' => null,

            'status' => NewsStatus::Draft->value,

            'is_featured' => false,

            'published_at' => null,

            'submitted_at' => null,

            'approved_at' => null,

            'archived_at' => null,

            'seo_title' => null,

            'seo_description' => null,

            'created_by' => null,

            'updated_by' => null,

            'submitted_by' => null,

            'approved_by' => null,

            'published_by' => null,

            'archived_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(
            fn (): array => [
                'status' => NewsStatus::Published->value,

                'published_at' => now(),
            ],
        );
    }

    public function featured(): static
    {
        return $this->state(
            fn (): array => [
                'is_featured' => true,
            ],
        );
    }
}
