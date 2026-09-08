<?php

namespace App\Models;

use App\Enums\PageLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $hero_slide_id
 * @property PageLocale $locale
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $button_label
 * @property string|null $button_url
 * @property-read HeroSlide $heroSlide
 */
final class HeroSlideTranslation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'locale',
        'title',
        'subtitle',
        'button_label',
        'button_url',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'hero_slide_id' => 'integer',
            'locale' => PageLocale::class,
        ];
    }

    /** @return BelongsTo<HeroSlide, $this> */
    public function heroSlide(): BelongsTo
    {
        return $this->belongsTo(HeroSlide::class);
    }
}
