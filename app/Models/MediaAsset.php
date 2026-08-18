<?php

namespace App\Models;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        self::creating(
            function (MediaAsset $media): void {
                $uuid = $media->getAttribute(
                    'uuid',
                );

                if (
                    ! is_string($uuid)
                    || trim($uuid) === ''
                ) {
                    $media->setAttribute(
                        'uuid',
                        Str::uuid()->toString(),
                    );
                }
            },
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,

            'source' => MediaSource::class,

            'visibility' => MediaVisibility::class,

            'metadata' => 'array',

            'size_bytes' => 'integer',

            'width' => 'integer',

            'height' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
        );
    }

    public function isImage(): bool
    {
        $type = $this->getAttribute(
            'type',
        );

        return $type instanceof MediaType
            && $type === MediaType::Image;
    }

    public function isDocument(): bool
    {
        $type = $this->getAttribute(
            'type',
        );

        return $type instanceof MediaType
            && $type === MediaType::Document;
    }

    public function isExternal(): bool
    {
        $source = $this->getAttribute(
            'source',
        );

        return $source instanceof MediaSource
            && $source === MediaSource::External;
    }

    public function isPublic(): bool
    {
        $visibility = $this->getAttribute(
            'visibility',
        );

        return $visibility instanceof MediaVisibility
            && $visibility === MediaVisibility::Public;
    }
}
