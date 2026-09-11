<?php

namespace App\Livewire\Admin\SchoolLeaders;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\SchoolLeader;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MediaUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class SchoolLeaderIndex extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $imageMediaId = '';

    /**
     * Livewire temporarily hydrates uploaded files internally,
     * therefore this property must remain mixed.
     */
    public mixed $newImage = null;

    public string $newImageTitle = '';

    public string $newImageAltText = '';

    public string $titleEn = '';

    public string $titleSi = '';

    public string $nameEn = '';

    public string $nameSi = '';

    public int $sortOrder = 1;

    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        $firstLeader = SchoolLeader::query()
            ->whereIn('role_key', SchoolLeader::allowedRoles())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($firstLeader instanceof SchoolLeader) {
            $this->edit($firstLeader->id);
        }
    }

    public function edit(int $id): void
    {
        Gate::authorize('settings.manage');

        $leader = SchoolLeader::query()->findOrFail($id);

        abort_unless(
            in_array($leader->role_key, SchoolLeader::allowedRoles(), true),
            404,
        );

        $this->editingId = $leader->id;

        $this->imageMediaId = $leader->image_media_id === null
            ? ''
            : (string) $leader->image_media_id;

        $this->titleEn = $leader->title_en;
        $this->titleSi = $leader->title_si ?? '';
        $this->nameEn = $leader->name_en ?? '';
        $this->nameSi = $leader->name_si ?? '';
        $this->sortOrder = $leader->sort_order;
        $this->isActive = $leader->is_active;

        $this->newImage = null;
        $this->newImageTitle = '';
        $this->newImageAltText = '';

        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        if ($this->editingId === null) {
            throw ValidationException::withMessages([
                'editingId' => 'Select a school leader to edit.',
            ]);
        }

        $validated = $this->validate([
            'imageMediaId' => [
                'nullable',
                'integer',
                'exists:media_assets,id',
            ],
            'newImage' => [
                'nullable',
                'file',
                'image',
                'max:20480',
            ],
            'newImageTitle' => [
                'nullable',
                'string',
                'max:255',
            ],
            'newImageAltText' => [
                'nullable',
                'string',
                'max:255',
            ],
            'titleEn' => [
                'required',
                'string',
                'max:120',
            ],
            'titleSi' => [
                'nullable',
                'string',
                'max:160',
            ],
            'nameEn' => [
                'nullable',
                'string',
                'max:180',
            ],
            'nameSi' => [
                'nullable',
                'string',
                'max:220',
            ],
            'sortOrder' => [
                'required',
                'integer',
                'min:1',
                'max:255',
            ],
            'isActive' => [
                'boolean',
            ],
        ]);

        $imageMediaId = $this->resolveImageMediaId($validated);
        $actor = $this->actor();

        $leader = DB::transaction(function () use (
            $actor,
            $imageMediaId,
        ): SchoolLeader {
            $leader = SchoolLeader::query()
                ->findOrFail($this->editingId);

            $leader->fill([
                'title_en' => $this->cleanRequired($this->titleEn),
                'title_si' => $this->cleanNullable($this->titleSi),
                'name_en' => $this->cleanNullable($this->nameEn),
                'name_si' => $this->cleanNullable($this->nameSi),
                'image_media_id' => $imageMediaId,
                'sort_order' => $this->sortOrder,
                'is_active' => $this->isActive,
                'updated_by' => $actor->id,
            ]);

            $leader->save();

            return $leader;
        });

        app(AuditLogger::class)->log(
            event: 'settings.school-leader-updated',
            description: 'A School of Signals leadership profile was updated.',
            actor: $actor,
            subject: $leader,
        );

        session()->flash(
            'status',
            'Leadership profile updated successfully.',
        );

        $this->edit($leader->id);
    }

    public function updatedNewImage(): void
    {
        $this->resetValidation('newImage');
    }

    public function clearNewImage(): void
    {
        $this->newImage = null;

        $this->resetValidation('newImage');
    }

    public function render(): View
    {
        return view(
            'livewire.admin.school-leaders.school-leader-index',
            [
                'schoolLeaders' => SchoolLeader::query()
                    ->whereIn('role_key', SchoolLeader::allowedRoles())
                    ->with('image')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(),
                'mediaAssets' => MediaAsset::query()
                    ->where('type', MediaType::Image->value)
                    ->where(
                        'visibility',
                        MediaVisibility::Public->value,
                    )
                    ->latest()
                    ->limit(200)
                    ->get(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'School Leadership',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveImageMediaId(array $validated): ?int
    {
        if ($this->newImage instanceof TemporaryUploadedFile) {
            Gate::authorize('media.upload');

            $title = $this->cleanNullable($this->newImageTitle)
                ?? $this->cleanNullable($this->nameEn)
                ?? $this->cleanRequired($this->titleEn);

            $altText = $this->cleanNullable($this->newImageAltText)
                ?? $title;

            $media = app(MediaUploadService::class)->upload(
                file: $this->newImage,
                type: MediaType::Image,
                visibility: MediaVisibility::Public,
                actor: $this->actor(),
                title: $title,
                altText: $altText,
                caption: null,
            );

            return $media->id;
        }

        $selectedMediaId = $validated['imageMediaId'] ?? null;

        $imageMediaId = is_numeric($selectedMediaId)
            ? (int) $selectedMediaId
            : null;

        $this->assertPublicImage($imageMediaId);

        return $imageMediaId;
    }

    private function assertPublicImage(?int $mediaId): void
    {
        if ($mediaId === null) {
            return;
        }

        $exists = MediaAsset::query()
            ->whereKey($mediaId)
            ->where('type', MediaType::Image->value)
            ->where(
                'visibility',
                MediaVisibility::Public->value,
            )
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'imageMediaId' => 'Select a valid public image.',
            ]);
        }
    }

    private function actor(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function cleanRequired(string $value): string
    {
        return trim($value);
    }

    private function cleanNullable(string $value): ?string
    {
        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }
}
