<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $source_path
 * @property string $destination_url
 * @property int $http_status
 * @property bool $is_active
 * @property int $hit_count
 * @property CarbonImmutable|null $last_hit_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
final class Redirect extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'source_path',
        'destination_url',
        'http_status',
        'is_active',
        'hit_count',
        'last_hit_at',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        self::creating(static function (Redirect $redirect): void {
            $uuid = $redirect->getAttribute('uuid');

            if (! is_string($uuid) || trim($uuid) === '') {
                $redirect->setAttribute(
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
            'http_status' => 'integer',
            'is_active' => 'boolean',
            'hit_count' => 'integer',
            'last_hit_at' => 'immutable_datetime',
        ];
    }

    /**
     * @param  Builder<Redirect>  $query
     * @return Builder<Redirect>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
