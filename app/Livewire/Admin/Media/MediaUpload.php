<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\User;
use App\Services\MediaUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class MediaUpload extends Component
{
    use WithFileUploads;

    /*
     * Keep this property mixed because Livewire temporarily
     * hydrates/dehydrates uploaded files internally.
     */
    public mixed $file = null;

    public string $type = 'image';

    public string $visibility = 'public';

    public string $title = '';

    public string $altText = '';

    public string $caption = '';

    public function mount(): void
    {
        Gate::authorize(
            'media.upload',
        );
    }

    public function updatedType(): void
    {
        $this->resetValidation();

        if ($this->type !== MediaType::Image->value) {
            $this->altText = '';
        }
    }

    public function updatedFile(): void
    {
        $this->resetValidation(
            'file',
        );
    }

    public function clearFile(): void
    {
        $this->file = null;

        $this->resetValidation(
            'file',
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'media.upload',
        );

        $this->normaliseInput();

        $this->validate();

        if (! $this->file instanceof TemporaryUploadedFile) {
            throw ValidationException::withMessages([
                'file' => 'Please select a valid file to upload.',
            ]);
        }

        $mediaType = MediaType::tryFrom(
            $this->type,
        );

        if (
            ! $mediaType instanceof MediaType
            || ! in_array(
                $mediaType,
                [
                    MediaType::Image,
                    MediaType::Document,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'type' => 'Please select a supported media type.',
            ]);
        }

        $visibility = MediaVisibility::tryFrom(
            $this->visibility,
        );

        if (! $visibility instanceof MediaVisibility) {
            throw ValidationException::withMessages([
                'visibility' => 'Please select a valid visibility.',
            ]);
        }

        $media = app(
            MediaUploadService::class,
        )->upload(
            file: $this->file,
            type: $mediaType,
            visibility: $visibility,
            actor: $this->actor(),

            title: $this->title !== ''
                ? $this->title
                : null,

            altText: $mediaType === MediaType::Image
                && $this->altText !== ''
                ? $this->altText
                : null,

            caption: $this->caption !== ''
                ? $this->caption
                : null,
        );

        $uploadedTitle =
            (string) $media->title;

        $this->reset([
            'file',
            'title',
            'altText',
            'caption',
        ]);

        $this->type =
            MediaType::Image->value;

        $this->visibility =
            MediaVisibility::Public->value;

        session()->flash(
            'status',
            sprintf(
                '%s was uploaded successfully.',
                $uploadedTitle,
            ),
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',

                /*
                 * Livewire temporary maximum.
                 *
                 * The secure service applies the
                 * smaller per-media-type limits.
                 */
                'max:20480',
            ],

            'type' => [
                'required',

                Rule::in([
                    MediaType::Image->value,
                    MediaType::Document->value,
                ]),
            ],

            'visibility' => [
                'required',

                Rule::in([
                    MediaVisibility::Public->value,
                    MediaVisibility::Internal->value,
                    MediaVisibility::Restricted->value,
                ]),
            ],

            'title' => [
                'nullable',
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
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'file.required' => 'Please choose an image or PDF file.',

            'file.file' => 'The selected upload must be a valid file.',

            'file.max' => 'The selected file is too large.',

            'type.in' => 'Only Image and Document uploads are currently supported.',

            'visibility.in' => 'Please select a valid media visibility.',
        ];
    }

    public function render(): View
    {
        return view(
            'livewire.admin.media.media-upload',
            [
                'mediaTypes' => [
                    MediaType::Image,
                    MediaType::Document,
                ],

                'visibilities' => MediaVisibility::cases(),

                'previewUrl' => $this->imagePreviewUrl(),

                'selectedFileName' => $this->selectedFileName(),

                'selectedFileSize' => $this->selectedFileSize(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Upload Media',
            ],
        );
    }

    private function normaliseInput(): void
    {
        $this->type = strtolower(
            trim($this->type),
        );

        $this->visibility = strtolower(
            trim($this->visibility),
        );

        $this->title = trim(
            $this->title,
        );

        $this->altText = trim(
            $this->altText,
        );

        $this->caption = trim(
            $this->caption,
        );
    }

    private function imagePreviewUrl(): ?string
    {
        if (! $this->file instanceof TemporaryUploadedFile) {
            return null;
        }

        $mimeType = strtolower(
            $this->file->getMimeType(),
        );

        if (
            ! str_starts_with(
                $mimeType,
                'image/',
            )
        ) {
            return null;
        }

        try {
            return $this->file->temporaryUrl();
        } catch (\Throwable) {
            return null;
        }
    }

    private function selectedFileName(): ?string
    {
        if (! $this->file instanceof TemporaryUploadedFile) {
            return null;
        }

        $name = trim(
            $this->file->getClientOriginalName(),
        );

        return $name !== ''
            ? $name
            : null;
    }

    private function selectedFileSize(): ?string
    {
        if (! $this->file instanceof TemporaryUploadedFile) {
            return null;
        }

        $bytes = $this->file->getSize();

        if ($bytes < 0) {
            return null;
        }

        if ($bytes >= 1024 * 1024) {
            return number_format(
                $bytes / (1024 * 1024),
                2,
            ).' MB';
        }

        return number_format(
            $bytes / 1024,
            1,
        ).' KB';
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
}
