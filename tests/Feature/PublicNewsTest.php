<?php

use App\Enums\NewsStatus;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Public News Index
|--------------------------------------------------------------------------
*/

test(
    'guests can access the public news index',
    function (): void {
        $response =
            $this->get(
                route('news.index'),
            );

        $response
            ->assertOk()
            ->assertSee(
                'News',
            );
    },
);

test(
    'public news index only shows currently published articles',
    function (): void {
        $published =
            News::factory()->create([
                'title' => 'Current Published Article',

                'slug' => 'current-published-article',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $future =
            News::factory()->create([
                'title' => 'Future Scheduled Article',

                'slug' => 'future-scheduled-article',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->addDay(),

                'featured_image_id' => null,
            ]);

        $draft =
            News::factory()->create([
                'title' => 'Draft Article',

                'slug' => 'draft-article',

                'status' => NewsStatus::Draft->value,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $submitted =
            News::factory()->create([
                'title' => 'Submitted Article',

                'slug' => 'submitted-article',

                'status' => NewsStatus::Submitted->value,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $changesRequested =
            News::factory()->create([
                'title' => 'Changes Requested Article',

                'slug' => 'changes-requested-article',

                'status' => NewsStatus::ChangesRequested->value,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $approved =
            News::factory()->create([
                'title' => 'Approved Article',

                'slug' => 'approved-article',

                'status' => NewsStatus::Approved->value,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $archived =
            News::factory()->create([
                'title' => 'Archived Article',

                'slug' => 'archived-article',

                'status' => NewsStatus::Archived->value,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $withoutPublicationDate =
            News::factory()->create([
                'title' => 'Published Without Date',

                'slug' => 'published-without-date',

                'status' => NewsStatus::Published->value,

                'published_at' => null,

                'featured_image_id' => null,
            ]);

        $response =
            $this->get(
                route('news.index'),
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
                $submitted->title,
            )
            ->assertDontSee(
                $changesRequested->title,
            )
            ->assertDontSee(
                $approved->title,
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
| Soft Deleted News
|--------------------------------------------------------------------------
*/

test(
    'soft deleted published articles are hidden from the public index',
    function (): void {
        $deleted =
            News::factory()->create([
                'title' => 'Deleted Published Article',

                'slug' => 'deleted-published-article',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $deleted->delete();

        $response =
            $this->get(
                route('news.index'),
            );

        $response
            ->assertOk()
            ->assertDontSee(
                'Deleted Published Article',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public News Detail
|--------------------------------------------------------------------------
*/

test(
    'guests can read a currently published news article',
    function (): void {
        $news =
            News::factory()->create([
                'title' => 'Public News Article',

                'slug' => 'public-news-article',

                'summary' => 'A public news article summary.',

                'content' => '<p>Public article body content.</p>',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $response =
            $this->get(
                route(
                    'news.show',
                    [
                        'slug' => $news->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                'Public News Article',
            )
            ->assertSee(
                'A public news article summary.',
            )
            ->assertSee(
                'Public article body content.',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Workflow Visibility Protection
|--------------------------------------------------------------------------
*/

test(
    'draft articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'hidden-draft-news',

                'status' => NewsStatus::Draft->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'submitted articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'hidden-submitted-news',

                'status' => NewsStatus::Submitted->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'changes requested articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'hidden-changes-requested-news',

                'status' => NewsStatus::ChangesRequested->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'approved articles cannot be viewed publicly before publication',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'hidden-approved-news',

                'status' => NewsStatus::Approved->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();
    },
);

test(
    'archived articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'hidden-archived-news',

                'status' => NewsStatus::Archived->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
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
    'future scheduled published articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'title' => 'Tomorrow Scheduled News',

                'slug' => 'tomorrow-scheduled-news',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->addDay(),

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();

        $this->get(
            route('news.index'),
        )
            ->assertOk()
            ->assertDontSee(
                'Tomorrow Scheduled News',
            );
    },
);

test(
    'published articles without a publication date cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'published-with-no-date',

                'status' => NewsStatus::Published->value,

                'published_at' => null,

                'featured_image_id' => null,
            ]);

        $this->get(
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| Deleted Article Protection
|--------------------------------------------------------------------------
*/

test(
    'soft deleted published articles cannot be viewed publicly',
    function (): void {
        $news =
            News::factory()->create([
                'slug' => 'deleted-public-news',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $news->delete();

        $this->get(
            route(
                'news.show',
                [
                    'slug' => 'deleted-public-news',
                ],
            ),
        )->assertNotFound();
    },
);

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/

test(
    'public news detail renders article seo metadata',
    function (): void {
        $news =
            News::factory()->create([
                'title' => 'Normal News Title',

                'slug' => 'seo-news-article',

                'summary' => 'Normal news summary.',

                'seo_title' => 'Special SEO News Title',

                'seo_description' => 'Special SEO description for this news article.',

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $url =
            route(
                'news.show',
                [
                    'slug' => $news->slug,
                ],
            );

        $response =
            $this->get(
                $url,
            );

        $response
            ->assertOk()
            ->assertSee(
                'Special SEO News Title',
            )
            ->assertSee(
                'Special SEO description for this news article.',
            )
            ->assertSee(
                $url,
            );
    },
);

test(
    'public news detail falls back to normal title and summary for seo',
    function (): void {
        $news =
            News::factory()->create([
                'title' => 'Fallback SEO Title',

                'slug' => 'fallback-seo-news',

                'summary' => 'Fallback SEO summary text.',

                'seo_title' => null,

                'seo_description' => null,

                'status' => NewsStatus::Published->value,

                'published_at' => now()->subHour(),

                'featured_image_id' => null,
            ]);

        $response =
            $this->get(
                route(
                    'news.show',
                    [
                        'slug' => $news->slug,
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertSee(
                'Fallback SEO Title',
            )
            ->assertSee(
                'Fallback SEO summary text.',
            );
    },
);

/*
|--------------------------------------------------------------------------
| Public Ordering
|--------------------------------------------------------------------------
*/

test(
    'featured published news is prioritised on the public news index',
    function (): void {
        $normal =
            News::factory()->create([
                'title' => 'Newest Normal Article',

                'slug' => 'newest-normal-article',

                'status' => NewsStatus::Published->value,

                'is_featured' => false,

                'published_at' => now()->subMinute(),

                'featured_image_id' => null,
            ]);

        $featured =
            News::factory()->create([
                'title' => 'Older Featured Article',

                'slug' => 'older-featured-article',

                'status' => NewsStatus::Published->value,

                'is_featured' => true,

                'published_at' => now()->subDay(),

                'featured_image_id' => null,
            ]);

        $response =
            $this->get(
                route('news.index'),
            );

        $response
            ->assertOk()
            ->assertSeeInOrder([
                $featured->title,
                $normal->title,
            ]);
    },
);
