<?php

namespace App\Livewire\Admin\Galleries;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use App\Services\MediaUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class GalleryCreate extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $titleSi = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $images = [];

    public function mount(): void
    {
        Gate::authorize('galleries.create');
    }

    public function removeImage(int $index): void
    {
        Gate::authorize('galleries.create');
        unset($this->images[$index]);
        $this->images = array_values($this->images);
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('galleries.create');
        Gate::authorize('galleries.update');
        Gate::authorize('media.upload');

        $this->title = trim($this->title);
        $this->validate();
        $actor = $this->actor();
        $service = app(GalleryService::class);

        // Media uploads use their own storage/DB lifecycle. If album creation
        // fails, successfully uploaded assets remain available in Media Library.
        /** @var list<MediaAsset> $uploaded */
        $uploaded = [];
        foreach ($this->images as $index => $file) {
            $uploaded[] = app(MediaUploadService::class)->upload(
                file: $file,
                type: MediaType::Image,
                visibility: MediaVisibility::Public,
                actor: $actor,
                title: mb_substr($this->title, 0, 240).' - '.($index + 1),
                altText: $this->title,
                caption: null,
            );
        }

        $gallery = DB::transaction(function () use ($actor, $service, $uploaded): Gallery {
            $gallery = $service->create(actor: $actor, title: $this->title, titleSi: $this->titleSi);
            foreach ($uploaded as $media) {
                $service->addImage(gallery: $gallery, media: $media, actor: $actor);
            }

            // Select the first attached image as cover through the existing service.
            return $service->update(
                gallery: $gallery,
                actor: $actor,
                title: $this->title,
                coverMedia: $uploaded[0],
            );
        });

        $this->reset('title', 'titleSi', 'images');
        session()->flash('status', 'Gallery created with images. The first image is the cover. Publish when ready.');
        $this->redirectRoute('admin.galleries.edit', ['gallery' => $gallery->id], navigate: true);
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'titleSi' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }

    public function render(): View
    {
        Gate::authorize('galleries.create');

        return view('livewire.admin.galleries.gallery-create')
            ->layout('components.layouts.admin', ['title' => 'Create Gallery']);
    }

    private function actor(): User
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        return $actor;
    }
}
