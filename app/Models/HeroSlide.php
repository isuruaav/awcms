<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string|null $subtitle
 * @property int|null $image_media_id
 * @property string|null $button_label
 * @property string|null $button_url
 * @property bool $is_active
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 */
final class HeroSlide extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'subtitle',
        'image_media_id',
        'button_label',
        'button_url',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        self::creating(static function (HeroSlide $slide): void {
            $uuid = $slide->getAttribute('uuid');

            if (! is_string($uuid) || trim($uuid) === '') {
                $slide->setAttribute(
                    'uuid',
                    (string) Str::uuid(),
                );
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'image_media_id' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_media_id');
    }

    /**
     * @param  Builder<HeroSlide>  $query
     * @return Builder<HeroSlide>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
