<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $site_name
 * @property string|null $site_tagline
 * @property string $theme_family
 * @property int|null $logo_media_id
 * @property int|null $favicon_media_id
 * @property string|null $address
 * @property string|null $phone_primary
 * @property string|null $phone_secondary
 * @property string|null $email
 * @property string|null $map_url
 * @property string|null $commander_name
 * @property string|null $commander_title
 * @property string|null $commander_message
 * @property int|null $commander_image_media_id
 * @property string $primary_color
 * @property string $accent_color
 * @property string|null $footer_text
 * @property string|null $default_seo_title
 * @property string|null $default_seo_description
 * @property bool $maintenance_mode
 * @property string|null $maintenance_message
 * @property int|null $updated_by
 */
final class SiteSetting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'site_name',
        'site_tagline',
        'theme_family',
        'logo_media_id',
        'favicon_media_id',
        'address',
        'phone_primary',
        'phone_secondary',
        'email',
        'map_url',
        'commander_name',
        'commander_title',
        'commander_message',
        'commander_image_media_id',
        'primary_color',
        'accent_color',
        'footer_text',
        'default_seo_title',
        'default_seo_description',
        'maintenance_mode',
        'maintenance_message',
        'updated_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'logo_media_id' => 'integer',
            'favicon_media_id' => 'integer',
            'commander_image_media_id' => 'integer',
            'maintenance_mode' => 'boolean',
        ];
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function logo(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'logo_media_id');
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function favicon(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'favicon_media_id');
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function commanderImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'commander_image_media_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $setting = self::query()->first();

        if ($setting instanceof self) {
            return $setting;
        }

        return self::query()->create([
            'site_name' => (string) config('app.name', 'Official Website'),
            'theme_family' => 'army-unit',
            'primary_color' => '#166534',
            'accent_color' => '#ca8a04',
        ]);
    }
}
