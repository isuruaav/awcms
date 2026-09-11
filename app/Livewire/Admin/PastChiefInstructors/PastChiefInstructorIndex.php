<?php

namespace App\Livewire\Admin\PastChiefInstructors;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\PastChiefInstructor;
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

final class PastChiefInstructorIndex extends Component
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

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        Gate::authorize('settings.manage');

        $instructor = PastChiefInstructor::query()
            ->findOrFail($id);

        $this->editingId = $instructor->id;

        $this->imageMediaId = $instructor->image_media_id === null
            ? ''
            : (string) $instructor->image_media_id;

        $this->nameEn = $instructor->name_en;
        $this->nameSi = $instructor->name_si ?? '';
        $this->fromDate = $instructor->from_date->format('Y-m-d');
        $this->toDate = $instructor->to_date->format('Y-m-d');

        $this->newImage = null;
        $this->newImageTitle = '';
        $this->newImageAltText = '';

        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

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
            'nameEn' => [
                'required',
                'string',
                'max:180',
            ],
            'nameSi' => [
                'nullable',
                'string',
                'max:220',
            ],
            'fromDate' => [
                'required',
                'date',
            ],
            'toDate' => [
                'required',
                'date',
                'after_or_equal:fromDate',
            ],
        ]);

        $imageMediaId = $this->resolveImageMediaId($validated);
        $actor = $this->actor();
        $wasCreated = $this->editingId === null;

        $instructor = DB::transaction(function () use (
            $actor,
            $imageMediaId,
        ): PastChiefInstructor {
            $instructor = $this->editingId === null
                ? new PastChiefInstructor
                : PastChiefInstructor::query()
                    ->findOrFail($this->editingId);

            if (! $instructor->exists) {
                $instructor->created_by = $actor->id;
            }

            $instructor->fill([
                'name_en' => $this->cleanRequired($this->nameEn),
                'name_si' => $this->cleanNullable($this->nameSi),
                'from_date' => $this->fromDate,
                'to_date' => $this->toDate,
                'image_media_id' => $imageMediaId,
                'updated_by' => $actor->id,
            ]);

            $instructor->save();

            return $instructor;
        });

        app(AuditLogger::class)->log(
            event: $wasCreated
                ? 'content.past-chief-instructor-created'
                : 'content.past-chief-instructor-updated',
            description: $wasCreated
                ? 'A past chief instructor profile was created.'
                : 'A past chief instructor profile was updated.',
            actor: $actor,
            subject: $instructor,
        );

        session()->flash(
            'status',
            $wasCreated
                ? 'Past chief instructor added successfully.'
                : 'Past chief instructor updated successfully.',
        );

        $this->edit($instructor->id);
    }

    public function delete(int $id): void
    {
        Gate::authorize('settings.manage');

        $instructor = PastChiefInstructor::query()
            ->findOrFail($id);

        $actor = $this->actor();

        DB::transaction(function () use (
            $actor,
            $instructor,
        ): void {
            app(AuditLogger::class)->log(
                event: 'content.past-chief-instructor-deleted',
                description: 'A past chief instructor profile was deleted.',
                actor: $actor,
                subject: $instructor,
            );

            $instructor->delete();
        });

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash(
            'status',
            'Past chief instructor deleted successfully.',
        );
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
        Gate::authorize('settings.manage');

        return view(
            'livewire.admin.past-chief-instructors.past-chief-instructor-index',
            [
                'pastChiefInstructors' => PastChiefInstructor::query()
                    ->with('image')
                    ->orderByDesc('to_date')
                    ->orderByDesc('from_date')
                    ->orderByDesc('id')
                    ->get(),
                'mediaAssets' => MediaAsset::query()
                    ->where(
                        'type',
                        MediaType::Image->value,
                    )
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
                'title' => 'Past Chief Instructors',
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
                ?? $this->cleanRequired($this->nameEn);

            $altText = $this->cleanNullable(
                $this->newImageAltText,
            ) ?? $title;

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
            ->where(
                'type',
                MediaType::Image->value,
            )
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

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->imageMediaId = '';
        $this->newImage = null;
        $this->newImageTitle = '';
        $this->newImageAltText = '';
        $this->nameEn = '';
        $this->nameSi = '';
        $this->fromDate = '';
        $this->toDate = '';

        $this->resetValidation();
    }

    private function actor(): User
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof User,
            403,
        );

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
