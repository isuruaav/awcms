<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaMetadataService;
use App\Services\MediaReplacementService;
use App\Services\MediaUploadSecurity;
use App\Services\MediaUrlService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

final class MediaEdit extends Component
{
    use WithFileUploads;

    public ?MediaAsset $media = null;

    public string $title = '';

    public string $altText = '';

    public string $caption = '';

    public string $visibility = 'public';

    public bool $canUpdate = false;

    public bool $canReplace = false;

    public mixed $replacementFile = null;

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

        /*
         * External media has no local physical file
         * to replace.
         */
        $this->canReplace =
            Gate::allows(
                'media.replace',
            )
            && ! $media->isExternal();

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
         * Visibility changes may move the original
         * and variants between storage disks.
         */
        $this->loadMedia();

        session()->flash(
            'status',
            'Media details were updated successfully.',
        );
    }

    public function replaceFile(): void
    {
        Gate::authorize(
            'media.replace',
        );

        $media = $this->mediaAsset();

        if ($media->isExternal()) {
            throw ValidationException::withMessages([
                'replacementFile' => 'External media does not have a local file to replace.',
            ]);
        }

        /*
         * Livewire performs a first layer of temporary
         * upload validation.
         *
         * MediaReplacementService performs the final
         * server-side MIME, extension, checksum and
         * image safety validation.
         */
        $this->validate(
            $this->replacementRules(),
        );

        $file =
            $this->replacementFile;

        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'replacementFile' => 'Please select a valid replacement file.',
            ]);
        }

        $this->media = app(
            MediaReplacementService::class,
        )->replace(
            media: $media,

            file: $file,

            actor: $this->actor(),
        );

        /*
         * Clear the temporary Livewire upload after
         * successful replacement.
         */
        $this->reset(
            'replacementFile',
        );

        $this->resetValidation(
            'replacementFile',
        );

        /*
         * Replacement changes the physical file,
         * checksum, dimensions and image variants.
         *
         * Reload all displayed component state.
         */
        $this->loadMedia();

        session()->flash(
            'status',
            'Media file was replaced successfully.',
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
                    MediaVisibility::Public
                        ->value,

                    MediaVisibility::Internal
                        ->value,

                    MediaVisibility::Restricted
                        ->value,
                ]),
            ],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function replacementRules(): array
    {
        return [
            'replacementFile' => [
                'required',

                'file',

                'max:'
                    .$this
                        ->replacementMaximumKilobytes(),
            ],
        ];
    }

    public function render(): View
    {
        $media = $this->mediaAsset();

        /*
         * Variants are needed by MediaUrlService.
         */
        $media->loadMissing([
            'uploader',
            'variants',
        ]);

        $publicUrl = null;

        /*
         * Public images:
         * medium WebP -> original fallback.
         *
         * Private images and documents do not expose
         * a direct public preview URL.
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

                'replacementMaxKb' => $this
                    ->replacementMaximumKilobytes(),

                'replacementAccept' => $this
                    ->replacementAccept(
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
         * Prevent stale relations after visibility
         * changes or physical file replacement.
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
            : MediaVisibility::Public
                ->value;
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

    private function replacementMaximumKilobytes(): int
    {
        $type = $this
            ->mediaAsset()
            ->getAttribute(
                'type',
            );

        if (! $type instanceof MediaType) {
            return 20480;
        }

        $maximumKilobytes = app(
            MediaUploadSecurity::class,
        )->maximumKilobytes(
            $type,
        );

        /*
         * Service-level validation will reject
         * improperly configured upload types.
         *
         * This fallback prevents an invalid Livewire
         * max rule from being generated.
         */
        return $maximumKilobytes > 0
            ? $maximumKilobytes
            : 20480;
    }

    private function replacementAccept(
        MediaAsset $media,
    ): string {
        $type = $media->getAttribute(
            'type',
        );

        if (! $type instanceof MediaType) {
            return '';
        }

        $mimeExtensionMap = app(
            MediaUploadSecurity::class,
        )->mimeExtensionMap(
            $type,
        );

        $extensions = [];

        foreach (
            $mimeExtensionMap as $allowedExtensions
        ) {
            foreach (
                $allowedExtensions as $extension
            ) {
                $extension = strtolower(
                    trim(
                        $extension,
                    ),
                );

                if ($extension === '') {
                    continue;
                }

                $extensions[] =
                    '.'.$extension;
            }
        }

        return implode(
            ',',
            array_values(
                array_unique(
                    $extensions,
                ),
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
