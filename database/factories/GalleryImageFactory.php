<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryImage>
 */
final class GalleryImageFactory extends Factory
{
    protected $model =
        GalleryImage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_id' => Gallery::factory(),

            /*
             * Tests that create GalleryImage records should
             * explicitly provide an existing public image
             * MediaAsset ID.
             */
            'media_asset_id' => 1,

            'caption' => fake()->optional()->sentence(),

            'alt_text' => fake()->optional()->sentence(
                6,
            ),

            'sort_order' => fake()->numberBetween(
                0,
                50,
            ),

            'created_by' => null,

            'updated_by' => null,
        ];
    }
}
