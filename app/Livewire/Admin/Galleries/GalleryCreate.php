<?php

namespace App\Livewire\Admin\Galleries;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

final class GalleryCreate extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $eventDate = '';

    public string $description = '';

    public string $coverMediaId = '';

    public string $publishedAt = '';

    public string $seoTitle = '';

    public string $seoDescription = '';

    public function mount(): void
    {
        Gate::authorize(
            'galleries.create',
        );
    }

    public function save(): void
    {
        Gate::authorize(
            'galleries.create',
        );

        $this->normaliseInput();

        $this->validate();

        $coverMedia =
            $this->coverMedia();

        $gallery =
            app(
                GalleryService::class,
            )->create(
                actor: $this->actor(),

                title: $this->title,

                eventDate: $this->eventDate(),

                description: $this->nullableString(
                    $this->description,
                ),

                slug: $this->nullableString(
                    $this->slug,
                ),

                coverMedia: $coverMedia,

                publishedAt: $this->publicationDate(),

                seoTitle: $this->nullableString(
                    $this->seoTitle,
                ),

                seoDescription: $this->nullableString(
                    $this->seoDescription,
                ),
            );

        session()->flash(
            'status',
            'Gallery created successfully. You can now add images.',
        );

        $this->redirectRoute(
            'admin.galleries.edit',
            [
                'gallery' => $gallery->getKey(),
            ],
            navigate: true,
        );
    }

    public function render(): View
    {
        Gate::authorize(
            'galleries.create',
        );

        $mediaAssets =
            MediaAsset::query()
                ->where(
                    'type',
                    MediaType::Image->value,
                )
                ->where(
                    'visibility',
                    MediaVisibility::Public->value,
                )
                ->orderByDesc(
                    'id',
                )
                ->limit(
                    150,
                )
                ->get();

        return view(
            'livewire.admin.galleries.gallery-create',
            [
                'mediaAssets' => $mediaAssets,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Create Gallery',
            ],
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

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'eventDate' => [
                'nullable',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'coverMediaId' => [
                'nullable',
                'integer',
                'exists:media_assets,id',
            ],

            'publishedAt' => [
                'nullable',
                'date',
            ],

            'seoTitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seoDescription' => [
                'nullable',
                'string',
                'max:320',
            ],
        ];
    }

    private function normaliseInput(): void
    {
        $this->title =
            trim(
                $this->title,
            );

        $this->slug =
            trim(
                $this->slug,
            );

        $this->eventDate =
            trim(
                $this->eventDate,
            );

        $this->description =
            trim(
                $this->description,
            );

        $this->coverMediaId =
            trim(
                $this->coverMediaId,
            );

        $this->publishedAt =
            trim(
                $this->publishedAt,
            );

        $this->seoTitle =
            trim(
                $this->seoTitle,
            );

        $this->seoDescription =
            trim(
                $this->seoDescription,
            );
    }

    private function coverMedia(): ?MediaAsset
    {
        if ($this->coverMediaId === '') {
            return null;
        }

        return MediaAsset::withTrashed()
            ->findOrFail(
                (int) $this->coverMediaId,
            );
    }

    private function eventDate(): ?CarbonImmutable
    {
        if ($this->eventDate === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->eventDate,
        )->startOfDay();
    }

    private function publicationDate(): ?CarbonImmutable
    {
        if ($this->publishedAt === '') {
            return null;
        }

        return CarbonImmutable::parse(
            $this->publishedAt,
        );
    }

    private function nullableString(
        string $value,
    ): ?string {
        $value =
            trim(
                $value,
            );

        return $value !== ''
            ? $value
            : null;
    }

    private function actor(): User
    {
        $actor =
            Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }
}
