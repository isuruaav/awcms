<?php

namespace App\Models;

use App\Enums\MediaVariantPreset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MediaVariant extends Model
{
    /**
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => MediaVariantPreset::class,

            'width' => 'integer',

            'height' => 'integer',

            'size_bytes' => 'integer',

            'generated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class,
        );
    }
}
