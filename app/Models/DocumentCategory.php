<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class DocumentCategory extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',

            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::creating(
            static function (DocumentCategory $category): void {
                $uuid =
                    $category->getAttribute(
                        'uuid',
                    );

                if (
                    ! is_string($uuid)
                    || trim($uuid) === ''
                ) {
                    $category->setAttribute(
                        'uuid',
                        Str::uuid()->toString(),
                    );
                }
            },
        );
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(
            Document::class,
            'document_category_id',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
        );
    }

    /**
     * @param  Builder<DocumentCategory>  $query
     * @return Builder<DocumentCategory>
     */
    public function scopeActive(
        Builder $query,
    ): Builder {
        return $query->where(
            'is_active',
            true,
        );
    }

    /**
     * @param  Builder<DocumentCategory>  $query
     * @return Builder<DocumentCategory>
     */
    public function scopeOrdered(
        Builder $query,
    ): Builder {
        return $query
            ->orderBy(
                'sort_order',
            )
            ->orderBy(
                'name',
            )
            ->orderBy(
                'id',
            );
    }
}
