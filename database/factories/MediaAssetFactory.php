<?php

namespace Database\Factories;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
final class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storedName =
            fake()->uuid().'.jpg';

        return [
            'type' => MediaType::Image->value,

            'source' => MediaSource::Upload->value,

            'visibility' => MediaVisibility::Public->value,

            'title' => fake()->sentence(3),

            'alt_text' => fake()->sentence(5),

            'caption' => null,

            'disk' => 'public',

            'directory' => 'media/testing',

            'stored_name' => $storedName,

            'original_name' => 'example-image.jpg',

            'path' => 'media/testing/'.$storedName,

            'external_url' => null,

            'mime_type' => 'image/jpeg',

            'extension' => 'jpg',

            'size_bytes' => 1024,

            'width' => 1200,

            'height' => 800,

            'checksum' => hash(
                'sha256',
                fake()->uuid(),
            ),

            'metadata' => null,

            'uploaded_by' => null,
        ];
    }

    public function document(): static
    {
        return $this->state(
            fn (): array => [
                'type' => MediaType::Document->value,

                'title' => 'Example Document',

                'stored_name' => 'example.pdf',

                'original_name' => 'example.pdf',

                'path' => 'media/testing/example.pdf',

                'mime_type' => 'application/pdf',

                'extension' => 'pdf',

                'width' => null,

                'height' => null,
            ],
        );
    }

    public function externalVideo(): static
    {
        return $this->state(
            fn (): array => [
                'type' => MediaType::Video->value,

                'source' => MediaSource::External->value,

                'title' => 'External Video',

                'disk' => null,

                'directory' => null,

                'stored_name' => null,

                'original_name' => null,

                'path' => null,

                'external_url' => 'https://www.youtube.com/watch?v=example',

                'mime_type' => null,

                'extension' => null,

                'size_bytes' => null,

                'width' => null,

                'height' => null,

                'checksum' => null,
            ],
        );
    }
}
