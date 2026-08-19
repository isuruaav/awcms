<?php

namespace Database\Factories;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsRevision>
 */
final class NewsRevisionFactory extends Factory
{
    protected $model = NewsRevision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_id' => News::factory(),

            'revision_number' => 1,

            'category_id' => null,

            'title' => fake()->sentence(
                6,
            ),

            'slug' => fake()->unique()->slug(),

            'summary' => fake()->paragraph(),

            'content' => sprintf(
                '<p>%s</p><p>%s</p><p>%s</p>',
                fake()->paragraph(),
                fake()->paragraph(),
                fake()->paragraph(),
            ),

            'featured_image_id' => null,

            'is_featured' => false,

            'status' => NewsStatus::Draft,

            'published_at' => null,

            'seo_title' => null,

            'seo_description' => null,

            'created_by' => User::factory(),

            'reason' => 'Content updated.',
        ];
    }
}
