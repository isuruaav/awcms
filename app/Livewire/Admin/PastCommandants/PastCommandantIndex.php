<?php

namespace App\Livewire\Admin\PastCommandants;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\PastCommandant;
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

final class PastCommandantIndex extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $imageMediaId = '';

    public mixed $newImage = null;

    public string $newImageTitle = '';

    public string $newImageAltText = '';

    public string $nameEn = '';

    public string $nameSi = '';

    public string $fromDate = '';

    public string $toDate = '';

    public function mount(): void
    {
        Gate::authorize('settings.manage');
    }

    public function create(): void
    {
        Gate::authorize('settings.manage');

        $this->reset([
            'editingId',
            'imageMediaId',
            'newImage',
            'newImageTitle',
            'newImageAltText',
            'nameEn',
            'nameSi',
            'fromDate',
            'toDate',
        ]);
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('settings.manage');

        $commandant = PastCommandant::query()->findOrFail($id);

        $this->editingId = $commandant->id;
        $this->imageMediaId = $commandant->image_media_id === null ? '' : (string) $commandant->image_media_id;
        $this->nameEn = $commandant->name_en;
        $this->nameSi = $commandant->name_si ?? '';
        $this->fromDate = $commandant->from_date->format('Y-m-d');
        $this->toDate = $commandant->to_date->format('Y-m-d');
        $this->newImage = null;
        $this->newImageTitle = '';
        $this->newImageAltText = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        $validated = $this->validate([
            'imageMediaId' => ['nullable', 'integer', 'exists:media_assets,id'],
            'newImage' => ['nullable', 'file', 'image', 'max:20480'],
            'newImageTitle' => ['nullable', 'string', 'max:255'],
            'newImageAltText' => ['nullable', 'string', 'max:255'],
            'nameEn' => ['required', 'string', 'max:180'],
            'nameSi' => ['nullable', 'string', 'max:220'],
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
        ]);

        $imageMediaId = $this->resolveImageMediaId($validated);
        $actor = $this->actor();

        $commandant = DB::transaction(function () use ($actor, $imageMediaId): PastCommandant {
            $commandant = $this->editingId === null
                ? new PastCommandant
                : PastCommandant::query()->findOrFail($this->editingId);

            if (! $commandant->exists) {
                $commandant->created_by = $actor->id;
            }

            $commandant->fill([
                'name_en' => trim($this->nameEn),
                'name_si' => $this->cleanNullable($this->nameSi),
                'from_date' => $this->fromDate,
                'to_date' => $this->toDate,
                'image_media_id' => $imageMediaId,
                'updated_by' => $actor->id,
            ]);
            $commandant->save();

            return $commandant;
        });

        app(AuditLogger::class)->log(
            event: $this->editingId === null ? 'content.past-commandant-created' : 'content.past-commandant-updated',
            description: 'A past commandant profile was saved.',
            actor: $actor,
            subject: $commandant,
        );

        session()->flash('status', $this->editingId === null
            ? 'Past commandant added successfully.'
            : 'Past commandant updated successfully.');

        $this->edit($commandant->id);
    }

    public function delete(int $id): void
    {
        Gate::authorize('settings.manage');

        $commandant = PastCommandant::query()->findOrFail($id);
        $actor = $this->actor();
        $commandant->delete();

        app(AuditLogger::class)->log(
            event: 'content.past-commandant-deleted',
            description: 'A past commandant profile was deleted.',
            actor: $actor,
            subject: $commandant,
        );

        if ($this->editingId === $id) {
            $this->create();
        }

        session()->flash('status', 'Past commandant deleted successfully.');
    }

    public function clearNewImage(): void
    {
        $this->newImage = null;
        $this->resetValidation('newImage');
    }

    public function render(): View
    {
        Gate::authorize('settings.manage');

        return view('livewire.admin.past-commandants.past-commandant-index', [
            'pastCommandants' => PastCommandant::query()
                ->with('image')
                ->orderByDesc('to_date')
                ->orderByDesc('from_date')
                ->get(),
            'mediaAssets' => MediaAsset::query()
                ->where('type', MediaType::Image->value)
                ->where('visibility', MediaVisibility::Public->value)
                ->latest()
                ->limit(200)
                ->get(),
        ])->layout('components.layouts.admin', ['title' => 'Past Commandants']);
    }

    /** @param array<string, mixed> $validated */
    private function resolveImageMediaId(array $validated): ?int
    {
        if ($this->newImage instanceof TemporaryUploadedFile) {
            Gate::authorize('media.upload');

            $title = $this->cleanNullable($this->newImageTitle)
                ?? $this->cleanRequired($this->nameEn);
            $altText = $this->cleanNullable($this->newImageAltText) ?? $title;

            return app(MediaUploadService::class)->upload(
                file: $this->newImage,
                type: MediaType::Image,
                visibility: MediaVisibility::Public,
                actor: $this->actor(),
                title: $title,
                altText: $altText,
                caption: null,
            )->id;
        }

        $selectedMediaId = $validated['imageMediaId'] ?? null;
        $imageMediaId = is_numeric($selectedMediaId) ? (int) $selectedMediaId : null;
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
            ->where('visibility', MediaVisibility::Public->value)
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

        return $value === '' ? null : $value;
    }
}
