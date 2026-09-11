<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class NewsImageService
{
    public function addImage(
        News $news,
        MediaAsset $media,
        User $actor,
    ): NewsImage {
        Gate::forUser($actor)->authorize(
            'news.update',
        );

        $this->assertEditable($news);
        $this->assertMediaAllowed($media);

        $newsId = (int) $news->getKey();
        $mediaId = (int) $media->getKey();

        return DB::transaction(
            function () use (
                $newsId,
                $mediaId,
                $actor,
            ): NewsImage {
                $news = News::query()
                    ->lockForUpdate()
                    ->findOrFail($newsId);

                $this->assertEditable($news);

                $alreadyAttached = NewsImage::query()
                    ->where('news_id', $newsId)
                    ->where('media_asset_id', $mediaId)
                    ->exists();

                if ($alreadyAttached) {
                    throw ValidationException::withMessages([
                        'galleryUploads' => 'This image is already attached to the news article.',
                    ]);
                }

                $maximumSortOrder = NewsImage::query()
                    ->where('news_id', $newsId)
                    ->max('sort_order');

                $nextSortOrder = is_numeric($maximumSortOrder)
                    ? (int) $maximumSortOrder + 1
                    : 0;

                $newsImage = NewsImage::query()->create([
                    'news_id' => $newsId,
                    'media_asset_id' => $mediaId,
                    'sort_order' => $nextSortOrder,
                ]);

                app(AuditLogger::class)->log(
                    event: 'news.image-added',
                    description: 'An image was added to a news article.',
                    actor: $actor,
                    subject: $news,
                    oldValues: [],
                    newValues: [
                        'news_image_id' => (int) $newsImage->getKey(),
                        'media_asset_id' => $mediaId,
                        'sort_order' => $nextSortOrder,
                    ],
                );

                return $newsImage->refresh();
            },
            3,
        );
    }

    public function removeImage(
        NewsImage $newsImage,
        User $actor,
    ): void {
        Gate::forUser($actor)->authorize(
            'news.update',
        );

        $newsImageId = (int) $newsImage->getKey();

        DB::transaction(
            function () use (
                $newsImageId,
                $actor,
            ): void {
                $newsImage = NewsImage::query()
                    ->lockForUpdate()
                    ->findOrFail($newsImageId);

                $news = News::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $newsImage->news_id);

                $this->assertEditable($news);

                $mediaId = (int) $newsImage->media_asset_id;

                if ((int) $news->featured_image_id === $mediaId) {
                    $news->update([
                        'featured_image_id' => null,
                        'updated_by' => $actor->id,
                    ]);
                }

                $newsImage->delete();

                app(AuditLogger::class)->log(
                    event: 'news.image-removed',
                    description: 'An image was removed from a news article.',
                    actor: $actor,
                    subject: $news,
                    oldValues: [
                        'news_image_id' => $newsImageId,
                        'media_asset_id' => $mediaId,
                    ],
                    newValues: [],
                );
            },
            3,
        );
    }

    public function setFeaturedImage(
        NewsImage $newsImage,
        User $actor,
    ): void {
        Gate::forUser($actor)->authorize(
            'news.update',
        );

        $newsImageId = (int) $newsImage->getKey();

        DB::transaction(
            function () use (
                $newsImageId,
                $actor,
            ): void {
                $newsImage = NewsImage::query()
                    ->lockForUpdate()
                    ->findOrFail($newsImageId);

                $news = News::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $newsImage->news_id);

                $this->assertEditable($news);

                $news->update([
                    'featured_image_id' => (int) $newsImage->media_asset_id,
                    'updated_by' => $actor->id,
                ]);

                app(AuditLogger::class)->log(
                    event: 'news.featured-image-set',
                    description: 'The featured image was selected for a news article.',
                    actor: $actor,
                    subject: $news,
                    oldValues: [],
                    newValues: [
                        'news_image_id' => $newsImageId,
                        'media_asset_id' => (int) $newsImage->media_asset_id,
                    ],
                );
            },
            3,
        );
    }

    public function clearFeaturedImage(
        News $news,
        User $actor,
    ): void {
        Gate::forUser($actor)->authorize(
            'news.update',
        );

        $newsId = (int) $news->getKey();

        DB::transaction(
            function () use (
                $newsId,
                $actor,
            ): void {
                $news = News::query()
                    ->lockForUpdate()
                    ->findOrFail($newsId);

                $this->assertEditable($news);

                $news->update([
                    'featured_image_id' => null,
                    'updated_by' => $actor->id,
                ]);

                app(AuditLogger::class)->log(
                    event: 'news.featured-image-cleared',
                    description: 'The featured image was cleared from a news article.',
                    actor: $actor,
                    subject: $news,
                    oldValues: [],
                    newValues: [],
                );
            },
            3,
        );
    }

    private function assertEditable(News $news): void
    {
        $status = $news->getRawOriginal('status');

        if ($status !== NewsStatus::Draft->value) {
            throw ValidationException::withMessages([
                'galleryUploads' => 'Unpublish the article before changing its images.',
            ]);
        }
    }

    private function assertMediaAllowed(MediaAsset $media): void
    {
        if (
            $media->trashed()
            || ! $media->isImage()
            || ! $media->isPublic()
        ) {
            throw ValidationException::withMessages([
                'galleryUploads' => 'News images must be active Public images.',
            ]);
        }
    }
}
