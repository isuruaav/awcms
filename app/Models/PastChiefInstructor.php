<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name_en
 * @property string|null $name_si
 * @property Carbon $from_date
 * @property Carbon $to_date
 * @property int|null $image_media_id
 * @property int|null $created_by
 * @property int|null $updated_by
 */
final class PastChiefInstructor extends Model
{
    protected $fillable = [
        'name_en', 'name_si', 'from_date', 'to_date', 'image_media_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'image_media_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_media_id');
    }

    public function nameForLocale(string $locale): string
    {
        if ($locale === 'si' && is_string($this->name_si) && trim($this->name_si) !== '') {
            return $this->name_si;
        }

        return $this->name_en;
    }
}
