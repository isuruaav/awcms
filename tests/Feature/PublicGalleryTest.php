<?php

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $galleryAttributes
 * @param  array<string, mixed>  $mediaAttributes
 * @param  array<string, mixed>  $imageAttributes
 * @return array{
 *     gallery: Gallery,
 *     media: MediaAsset,
 *     image: GalleryImage
 * }
 */
function createPublicGalleryFixtureForPublicGalleryTest(
    array $galleryAttributes = [],
    array $mediaAttributes = [],
    array $imageAttributes = [],
): array {
    $media =
        MediaAsset::factory()->create(
            $mediaAttributes,
        );

    $gallery =
        Gallery::factory()->create(
            array_replace(
                [
                    'status' => GalleryStatus::Published->value,

                    'published_at' => now()->subHour(),

                    'cover_media_id' => (int) $media->getKey(),
                ],
                $galleryAttributes,
            ),
        );

    $image =
        GalleryImage::factory()->create(
            array_replace(
                [
                    'gallery_id' => (int) $gallery->getKey(),

                    'media_asset_id' => (int) $media->getKey(),

                    'alt_text' => null,

                    'sort_order' => 0,
                ],
                $imageAttributes,
            ),
        );

    return [
        'gallery' => $gallery,

        'media' => $media,

        'image' => $image,
    ];
}

/*
|--------------------------------------------------------------------------
| Public Gallery Index
|--------------------------------------------------------------------------
*/

test(
    'guests can access the public gallery index',
    function (): void {
        createPublicGalleryFixtureForPublicGalleryTest([
            'title' => 'Public Gallery',

            'slug' => 'public-gallery',
        ]);

        $response =
            $this->get(
                route('galleries.index'),
            );

        $response
            ->assertOk()
            ->assertSee(
                'Gallery',
            )
            ->assertSee(
                'Public Gallery',
            );
    },
);

test(
    'public gallery index only shows currently published galleries',
    function (): void {
        $published =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Current Published Gallery',

                'slug' => 'current-published-gallery',

                'status' => GalleryStatus::Published->value,

                'published_at' => now()->subHour(),
            ])['gallery'];

        $future =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Future Gallery',

                'slug' => 'future-gallery',

                'status' => GalleryStatus::Published->value,

                'published_at' => now()->addDay(),
            ])['gallery'];

        $draft =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Draft Gallery',

                'slug' => 'draft-gallery',

                'status' => GalleryStatus::Draft->value,

                'published_at' => now()->subDay(),
            ])['gallery'];

        $archived =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Archived Gallery',

                'slug' => 'archived-gallery',

                'status' => GalleryStatus::Archived->value,

                'published_at' => now()->subDay(),

                'archived_at' => now(),
            ])['gallery'];

        $withoutPublicationDate =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Published Gallery Without Date',

                'slug' => 'published-gallery-without-date',

                'status' => GalleryStatus::Published->value,

                'published_at' => null,
            ])['gallery'];

        $response =
            $this->get(
                route('galleries.index'),
            );

        $response
            ->assertOk()
            ->assertSee(
                $published->title,
            )
            ->assertDontSee(
                $future->title,
            )
            ->assertDontSee(
                $draft->title,
            )
            ->assertDontSee(
                $archived->title,
            )
            ->assertDontSee(
                $withoutPublicationDate->title,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Soft Deleted Galleries
|--------------------------------------------------------------------------
*/

test(
    'soft deleted published galleries are hidden from the public index',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Deleted Published Gallery',

                'slug' => 'deleted-published-gallery',
            ]);

        $fixture['gallery']->delete();

        $this->get(
            route('galleries.index'),
        )
            ->assertOk()
            ->assertDontSee(
                'Deleted Published Gallery',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public Gallery Detail
|--------------------------------------------------------------------------
*/

test(
    'guests can view a currently published gallery',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest(
                [
                    'title' => 'Public Gallery Detail',

                    'slug' => 'public-gallery-detail',

                    'description' => 'Public gallery detail description.',
                ],
                [
                    'alt_text' => 'Public Gallery Image',
                ],
            );

        $gallery =
            $fixture['gallery'];

        $response =
            $this->get(
                route(
                    'galleries.show',
                    [
                        'slug' => $gallery->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                'Public Gallery Detail',
            )
            ->assertSee(
                'Public gallery detail description.',
            )
            ->assertSee(
                'Public Gallery Image',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Workflow Visibility Protection
|--------------------------------------------------------------------------
*/

test(
    'draft galleries cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'slug' => 'hidden-draft-gallery',

                'status' => GalleryStatus::Draft->value,

                'published_at' => now()->subHour(),
            ]);

        $this->get(
            route(
                'galleries.show',
                [
                    'slug' => $fixture['gallery']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'archived galleries cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'slug' => 'hidden-archived-gallery',

                'status' => GalleryStatus::Archived->value,

                'published_at' => now()->subDay(),

                'archived_at' => now(),
            ]);

        $this->get(
            route(
                'galleries.show',
                [
                    'slug' => $fixture['gallery']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Scheduled Publication
|--------------------------------------------------------------------------
*/

test(
    'future scheduled published galleries cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Future Scheduled Gallery',

                'slug' => 'future-scheduled-gallery',

                'status' => GalleryStatus::Published->value,

                'published_at' => now()->addDay(),
            ]);

        $this->get(
            route(
                'galleries.show',
                [
                    'slug' => $fixture['gallery']->slug,
                ],
            ),
        )->assertNotFound();

        $this->get(
            route('galleries.index'),
        )
            ->assertOk()
            ->assertDontSee(
                'Future Scheduled Gallery',
            );
    },
);

test(
    'published galleries without a publication date cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'slug' => 'published-gallery-with-no-date',

                'status' => GalleryStatus::Published->value,

                'published_at' => null,
            ]);

        $this->get(
            route(
                'galleries.show',
                [
                    'slug' => $fixture['gallery']->slug,
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Deleted Gallery Protection
|--------------------------------------------------------------------------
*/

test(
    'soft deleted published galleries cannot be viewed publicly',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'slug' => 'deleted-public-gallery',
            ]);

        $fixture['gallery']->delete();

        $this->get(
            route(
                'galleries.show',
                [
                    'slug' => 'deleted-public-gallery',
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Cover Image And Public Link
|--------------------------------------------------------------------------
*/

test(
    'public gallery index renders the cover image and detail link',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest(
                [
                    'title' => 'Gallery With Cover',

                    'slug' => 'gallery-with-cover',
                ],
                [
                    'alt_text' => 'Gallery Cover Alt',
                ],
            );

        $gallery =
            $fixture['gallery'];

        $media =
            $fixture['media'];

        $detailUrl =
            route(
                'galleries.show',
                [
                    'slug' => $gallery->slug,
                ],
            );

        $response =
            $this->get(
                route('galleries.index'),
            );

        $response
            ->assertOk()
            ->assertSee(
                $detailUrl,
            )
            ->assertSee(
                $media->stored_name,
            )
            ->assertSee(
                'alt="Gallery Cover Alt"',
                false,
            );
    },
);

/*
|--------------------------------------------------------------------------
| Image Ordering
|--------------------------------------------------------------------------
*/

test(
    'public gallery detail renders images in configured order',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest(
                [
                    'title' => 'Ordered Gallery',

                    'slug' => 'ordered-gallery',
                ],
                [
                    'alt_text' => 'Third Ordered Image',
                ],
                [
                    'alt_text' => 'Third Ordered Image',

                    'sort_order' => 30,
                ],
            );

        $gallery =
            $fixture['gallery'];

        $firstMedia =
            MediaAsset::factory()->create([
                'alt_text' => 'First Media Alt',
            ]);

        GalleryImage::factory()->create([
            'gallery_id' => (int) $gallery->getKey(),

            'media_asset_id' => (int) $firstMedia->getKey(),

            'alt_text' => 'First Ordered Image',

            'sort_order' => 10,
        ]);

        $secondMedia =
            MediaAsset::factory()->create([
                'alt_text' => 'Second Media Alt',
            ]);

        GalleryImage::factory()->create([
            'gallery_id' => (int) $gallery->getKey(),

            'media_asset_id' => (int) $secondMedia->getKey(),

            'alt_text' => 'Second Ordered Image',

            'sort_order' => 20,
        ]);

        $response =
            $this->get(
                route(
                    'galleries.show',
                    [
                        'slug' => $gallery->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSeeInOrder([
                'First Ordered Image',

                'Second Ordered Image',

                'Third Ordered Image',
            ]);
    },
);

/*
|--------------------------------------------------------------------------
| Alt Text Fallback
|--------------------------------------------------------------------------
*/

test(
    'gallery image alt text follows the required fallback priority',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest(
                [
                    'title' => 'Alt Fallback Gallery',

                    'slug' => 'alt-fallback-gallery',
                ],
                [
                    'alt_text' => 'Media Alt Should Lose',
                ],
                [
                    'alt_text' => 'Gallery Image Alt Wins',

                    'sort_order' => 10,
                ],
            );

        $gallery =
            $fixture['gallery'];

        $mediaFallback =
            MediaAsset::factory()->create([
                'alt_text' => 'Media Asset Alt Wins',
            ]);

        GalleryImage::factory()->create([
            'gallery_id' => (int) $gallery->getKey(),

            'media_asset_id' => (int) $mediaFallback->getKey(),

            'alt_text' => null,

            'sort_order' => 20,
        ]);

        $titleFallback =
            MediaAsset::factory()->create([
                'alt_text' => null,
            ]);

        GalleryImage::factory()->create([
            'gallery_id' => (int) $gallery->getKey(),

            'media_asset_id' => (int) $titleFallback->getKey(),

            'alt_text' => null,

            'sort_order' => 30,
        ]);

        $response =
            $this->get(
                route(
                    'galleries.show',
                    [
                        'slug' => $gallery->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                'alt="Gallery Image Alt Wins"',
                false,
            )
            ->assertSee(
                'alt="Media Asset Alt Wins"',
                false,
            )
            ->assertSee(
                'alt="Alt Fallback Gallery"',
                false,
            );
    },
);

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/

test(
    'public gallery detail renders custom seo metadata',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Normal Gallery Title',

                'slug' => 'seo-gallery',

                'description' => 'Normal gallery description.',

                'seo_title' => 'Special Gallery SEO Title',

                'seo_description' => 'Special SEO description for this gallery.',
            ]);

        $gallery =
            $fixture['gallery'];

        $url =
            route(
                'galleries.show',
                [
                    'slug' => $gallery->slug,
                ],
            );

        $response =
            $this->get(
                $url,
            );

        $response
            ->assertOk()
            ->assertSee(
                '<title>Special Gallery SEO Title</title>',
                false,
            )
            ->assertSee(
                'Special SEO description for this gallery.',
            )
            ->assertSee(
                'rel="canonical"',
                false,
            )
            ->assertSee(
                $url,
            )
            ->assertSee(
                'property="og:title"',
                false,
            )
            ->assertSee(
                'property="og:description"',
                false,
            )
            ->assertSee(
                'property="og:url"',
                false,
            )
            ->assertSee(
                'property="og:image"',
                false,
            );
    },
);

test(
    'public gallery detail falls back to gallery title and description for seo',
    function (): void {
        $fixture =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Fallback Gallery SEO Title',

                'slug' => 'fallback-gallery-seo',

                'description' => '<p>Fallback gallery SEO description.</p>',

                'seo_title' => null,

                'seo_description' => null,
            ]);

        $gallery =
            $fixture['gallery'];

        $response =
            $this->get(
                route(
                    'galleries.show',
                    [
                        'slug' => $gallery->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                '<title>Fallback Gallery SEO Title</title>',
                false,
            )
            ->assertSee(
                'Fallback gallery SEO description.',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public Ordering
|--------------------------------------------------------------------------
*/

test(
    'public gallery index shows the most recently published galleries first',
    function (): void {
        $older =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Older Published Gallery',

                'slug' => 'older-published-gallery',

                'published_at' => now()->subDay(),
            ])['gallery'];

        $newer =
            createPublicGalleryFixtureForPublicGalleryTest([
                'title' => 'Newer Published Gallery',

                'slug' => 'newer-published-gallery',

                'published_at' => now()->subMinute(),
            ])['gallery'];

        $response =
            $this->get(
                route('galleries.index'),
            );

        $response
            ->assertOk()
            ->assertSeeInOrder([
                $newer->title,

                $older->title,
            ]);
    },
);
