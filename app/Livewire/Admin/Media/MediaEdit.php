<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaMetadataService;
use App\Services\MediaUrlService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class MediaEdit extends Component
{
    public ?MediaAsset $media = null;

    public string $title = '';

    public string $altText = '';

    public string $caption = '';

    public string $visibility = 'public';

    public bool $canUpdate = false;

    public function mount(
        MediaAsset $media,
    ): void {
        Gate::authorize(
            'media.view',
        );

        $this->media = $media;

        $this->canUpdate = Gate::allows(
            'media.update',
        );

        $this->loadMedia();
    }

    public function save(): void
    {
        Gate::authorize(
            'media.update',
        );

        $this->normaliseInput();

        $this->validate();

        $visibility =
            MediaVisibility::tryFrom(
                $this->visibility,
            );

        if (
            ! $visibility
                instanceof MediaVisibility
        ) {
            throw ValidationException::withMessages([
                'visibility' => 'Please select a valid visibility.',
            ]);
        }

        $this->media = app(
            MediaMetadataService::class,
        )->update(
            media: $this->mediaAsset(),

            actor: $this->actor(),

            title: $this->title,

            altText: $this->altText !== ''
                ? $this->altText
                : null,

            caption: $this->caption !== ''
                ? $this->caption
                : null,

            visibility: $visibility,
        );

        /*
         * The metadata service may have moved the
         * original and variants between disks.
         *
         * Reload the component state using the latest
         * persisted media values.
         */
        $this->loadMedia();

        session()->flash(
            'status',
            'Media details were updated successfully.',
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'altText' => [
                'nullable',
                'string',
                'max:255',
            ],

            'caption' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'visibility' => [
                'required',

                Rule::in([
                    MediaVisibility::Public->value,
                    MediaVisibility::Internal->value,
                    MediaVisibility::Restricted->value,
                ]),
            ],
        ];
    }

    public function render(): View
    {
        $media = $this->mediaAsset();

        /*
         * Variants are needed by MediaUrlService.
         *
         * loadMissing avoids unnecessary repeated
         * database queries during Livewire renders.
         */
        $media->loadMissing([
            'uploader',
            'variants',
        ]);

        $publicUrl = null;

        /*
         * Only images receive an inline image preview.
         *
         * Public images:
         * medium WebP -> original fallback
         *
         * Internal / Restricted images:
         * null
         *
         * Documents:
         * null
         */
        if ($media->isImage()) {
            $publicUrl = app(
                MediaUrlService::class,
            )->mediumOrOriginal(
                $media,
            );
        }

        return view(
            'livewire.admin.media.media-edit',
            [
                'mediaAsset' => $media,

                'visibilities' => MediaVisibility::cases(),

                'publicUrl' => $publicUrl,

                'fileSize' => $this->fileSize(
                    $media,
                ),

                'dimensions' => $this->dimensions(
                    $media,
                ),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Media Details',
            ],
        );
    }

    private function loadMedia(): void
    {
        $media = $this->mediaAsset();

        /*
         * Clear previously loaded relations so that
         * visibility/disk/variant changes made by the
         * metadata service are not represented by stale
         * relationship data on the next render.
         */
        $media->unsetRelation(
            'variants',
        );

        $media->unsetRelation(
            'uploader',
        );

        $this->title = (string) (
            $media->getAttribute(
                'title',
            )
            ?? ''
        );

        $this->altText = (string) (
            $media->getAttribute(
                'alt_text',
            )
            ?? ''
        );

        $this->caption = (string) (
            $media->getAttribute(
                'caption',
            )
            ?? ''
        );

        $visibility =
            $media->getAttribute(
                'visibility',
            );

        $this->visibility =
            $visibility
            instanceof MediaVisibility
            ? $visibility->value
            : MediaVisibility::Public->value;
    }

    private function normaliseInput(): void
    {
        $this->title = trim(
            $this->title,
        );

        $this->altText = trim(
            $this->altText,
        );

        $this->caption = trim(
            $this->caption,
        );

        $this->visibility = strtolower(
            trim(
                $this->visibility,
            ),
        );
    }

    private function mediaAsset(): MediaAsset
    {
        abort_unless(
            $this->media
                instanceof MediaAsset,
            404,
        );

        return $this->media;
    }

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }

    private function fileSize(
        MediaAsset $media,
    ): string {
        $bytes = $media->getAttribute(
            'size_bytes',
        );

        if (
            ! is_int($bytes)
            || $bytes < 0
        ) {
            return '—';
        }

        if ($bytes >= 1048576) {
            return number_format(
                $bytes / 1048576,
                2,
            ).' MB';
        }

        return number_format(
            $bytes / 1024,
            1,
        ).' KB';
    }

    private function dimensions(
        MediaAsset $media,
    ): string {
        $width = $media->getAttribute(
            'width',
        );

        $height = $media->getAttribute(
            'height',
        );

        if (
            ! is_int($width)
            || ! is_int($height)
            || $width <= 0
            || $height <= 0
        ) {
            return '—';
        }

        return sprintf(
            '%d × %d px',
            $width,
            $height,
        );
    }
}
