<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaMetadataService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

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

        return view(
            'livewire.admin.media.media-edit',
            [
                'mediaAsset' => $media,

                'visibilities' => MediaVisibility::cases(),

                'publicUrl' => $this->publicUrl(
                    $media,
                ),

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
            trim($this->visibility),
        );
    }

    private function mediaAsset(): MediaAsset
    {
        abort_unless(
            $this->media instanceof MediaAsset,
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

    private function publicUrl(
        MediaAsset $media,
    ): ?string {
        $visibility =
            $media->getAttribute(
                'visibility',
            );

        if (
            ! $visibility
                instanceof MediaVisibility
            || $visibility
                !== MediaVisibility::Public
        ) {
            return null;
        }

        $type = $media->getAttribute(
            'type',
        );

        if (
            ! $type instanceof MediaType
            || $type !== MediaType::Image
        ) {
            return null;
        }

        $disk = $media->getAttribute(
            'disk',
        );

        $path = $media->getAttribute(
            'path',
        );

        if (
            ! is_string($disk)
            || trim($disk) === ''
            || ! is_string($path)
            || trim($path) === ''
        ) {
            return null;
        }

        try {
            return Storage::disk(
                $disk,
            )->url(
                $path,
            );
        } catch (Throwable) {
            return null;
        }
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
