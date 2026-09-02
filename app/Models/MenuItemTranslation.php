<?php

namespace App\Models;

use App\Enums\MenuLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $menu_item_id
 * @property MenuLocale $locale
 * @property string $label
 * @property string|null $url
 */
final class MenuItemTranslation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'menu_item_id',
        'locale',
        'label',
        'url',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'locale' => MenuLocale::class,
        ];
    }

    /** @return BelongsTo<MenuItem, $this> */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
