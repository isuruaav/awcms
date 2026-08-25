<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class DocumentVersion extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'media_asset_id',
        'version',
        'version_label',
        'change_note',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::creating(
            static function (DocumentVersion $documentVersion): void {
                $uuid =
                    $documentVersion->getAttribute(
                        'uuid',
                    );

                if (
                    ! is_string($uuid)
                    || trim($uuid) === ''
                ) {
                    $documentVersion->setAttribute(
                        'uuid',
                        Str::uuid()->toString(),
                    );
                }
            },
        );
    }

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(
            Document::class,
        );
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class,
            'media_asset_id',
        );
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
}
