<?php

namespace Database\Factories;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
final class GalleryFactory extends Factory
{
    protected $model =
        Gallery::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title =
            fake()->unique()->sentence(
                4,
            );

        return [
            'title' => $title,

            'slug' => Str::slug(
                $title,
            )
                .'-'
                .fake()->unique()->numberBetween(
                    1000,
                    999999,
                ),

            'event_date' => fake()->dateTimeBetween(
                '-2 years',
                'now',
            ),

            'description' => fake()->paragraphs(
                2,
                true,
            ),

            'cover_media_id' => null,

            'status' => GalleryStatus::Draft->value,

            'published_at' => null,

            'archived_at' => null,

            'seo_title' => null,

            'seo_description' => null,

            'created_by' => null,

            'updated_by' => null,

            'published_by' => null,

            'archived_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(
            fn (): array => [
                'status' => GalleryStatus::Published->value,

                'published_at' => now()->subMinute(),
            ],
        );
    }

    public function archived(): static
    {
        return $this->state(
            fn (): array => [
                'status' => GalleryStatus::Archived->value,

                'published_at' => now()->subDay(),

                'archived_at' => now(),
            ],
        );
    }
}
