<?php

namespace App\Models;

use App\Enums\ThemeLayoutStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $theme_layout_id
 * @property int $revision_number
 * @property string|null $content_html
 * @property string|null $content_css
 * @property ThemeLayoutStatus $status
 * @property int|null $created_by
 * @property CarbonImmutable $created_at
 */
final class ThemeLayoutRevision extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $guarded = [
        'id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'status' => ThemeLayoutStatus::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ThemeLayout, $this> */
    public function themeLayout(): BelongsTo
    {
        return $this->belongsTo(ThemeLayout::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
