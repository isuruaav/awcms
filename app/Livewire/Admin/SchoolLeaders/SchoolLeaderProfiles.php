<?php

namespace App\Livewire\Admin\SchoolLeaders;

use App\Models\SchoolLeader;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LeadershipProfileContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class SchoolLeaderProfiles extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $selectedLeaderId = '';

    public bool $profileEnabled = false;

    public string $profileHtmlEn = '';

    public string $profileHtmlSi = '';

    public string $profileCss = '';

    #[Locked]
    public string $previewEn = '';

    #[Locked]
    public string $previewSi = '';

    public function mount(): void
    {
        Gate::authorize('school-leaders.view');
        $requestedId = request()->integer('leader');
        $leader = $requestedId > 0
            ? $this->leadersQuery()->findOrFail($requestedId)
            : $this->leadersQuery()->first();

        if ($leader instanceof SchoolLeader) {
            $this->loadLeader($leader);
        }
    }

    public function updatedSelectedLeaderId(): void
    {
        Gate::authorize('school-leaders.view');
        abort_unless(ctype_digit($this->selectedLeaderId), 404);
        $this->loadLeader($this->leadersQuery()->findOrFail((int) $this->selectedLeaderId));
    }

    public function insertTemplate(string $locale): void
    {
        $this->authorizeEditing();
        abort_unless(in_array($locale, ['en', 'si'], true), 404);
        $field = $locale === 'si' ? 'profileHtmlSi' : 'profileHtmlEn';

        if (trim($this->{$field}) !== '') {
            throw ValidationException::withMessages([$field => 'Clear this editor before inserting a starter template.']);
        }

        $heading = $locale === 'si' ? 'චරිතාපදානය' : 'Biography';
        $intro = $locale === 'si' ? 'නිලධාරියාගේ චරිතාපදානය මෙහි ඇතුළත් කරන්න.' : 'Add the officer\'s biography here.';
        $career = $locale === 'si' ? 'සේවා පළපුරුද්ද' : 'Service and Experience';
        $details = $locale === 'si' ? 'සේවා පළපුරුද්ද සහ සුදුසුකම් මෙහි ඇතුළත් කරන්න.' : 'Add service experience and qualifications here.';
        $this->{$field} = '<section class="rounded-2xl bg-white p-6 md:p-10">'."\n"
            .'  <h2 class="text-2xl font-bold text-blue-900">'.$heading.'</h2>'."\n"
            .'  <p class="mt-4 text-slate-700">'.$intro.'</p>'."\n"
            .'  <div class="mt-8 rounded-xl bg-blue-50 p-6">'."\n"
            .'    <h3 class="text-xl font-semibold text-blue-900">'.$career.'</h3>'."\n"
            .'    <p class="mt-3 text-slate-700">'.$details.'</p>'."\n"
            .'  </div>'."\n".'</section>';
    }

    public function preview(): void
    {
        $this->authorizeEditing();
        $this->validateContent();
        $content = app(LeadershipProfileContent::class)->prepare($this->profileHtmlEn, $this->profileHtmlSi, $this->profileCss);
        $this->updatePreviews($content);
    }

    public function save(): void
    {
        $this->authorizeEditing();
        $this->validateContent();
        $leader = $this->leadersQuery()->findOrFail($this->editingId);
        $content = app(LeadershipProfileContent::class)->prepare($this->profileHtmlEn, $this->profileHtmlSi, $this->profileCss);

        if ($this->profileEnabled) {
            foreach (['en', 'si'] as $locale) {
                if ($content['html_'.$locale] === '') {
                    throw ValidationException::withMessages([
                        $locale === 'en' ? 'profileHtmlEn' : 'profileHtmlSi' => 'Add meaningful profile content in both languages before enabling the page.',
                    ]);
                }
            }
        }

        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($leader, $content, $actor): void {
            $leader->fill([
                'profile_enabled' => $this->profileEnabled,
                'profile_html_en' => $content['html_en'],
                'profile_html_si' => $content['html_si'],
                'profile_css' => $content['css'],
                'profile_compiled_css' => $content['compiled_css'],
                'updated_by' => $actor->id,
            ])->save();

            app(AuditLogger::class)->log(
                event: 'school-leader.profile-updated',
                description: 'Leadership profile content and visibility updated.',
                actor: $actor,
                subject: $leader,
                newValues: ['profile_enabled' => $leader->profile_enabled],
            );
        });

        $this->profileHtmlEn = $content['html_en'];
        $this->profileHtmlSi = $content['html_si'];
        $this->profileCss = $content['css'];
        $this->updatePreviews($content);
        session()->flash('status', $this->profileEnabled ? 'Profile saved and enabled in English and Sinhala.' : 'Profile saved. The public profile is disabled.');
    }

    public function render(): View
    {
        Gate::authorize('school-leaders.view');

        return view('livewire.admin.school-leaders.school-leader-profiles', [
            'leaders' => $this->leadersQuery()->get(),
            'selectedLeader' => $this->editingId === null ? null : $this->leadersQuery()->find($this->editingId),
        ])->layout('components.layouts.admin', ['title' => 'Leadership Profiles']);
    }

    private function loadLeader(SchoolLeader $leader): void
    {
        $this->editingId = $leader->id;
        $this->selectedLeaderId = (string) $leader->id;
        $this->profileEnabled = $leader->profile_enabled;
        $this->profileHtmlEn = $leader->profile_html_en ?? '';
        $this->profileHtmlSi = $leader->profile_html_si ?? '';
        $this->profileCss = $leader->profile_css ?? '';
        $this->previewEn = '';
        $this->previewSi = '';
        $this->resetValidation();
    }

    private function authorizeEditing(): void
    {
        Gate::authorize('school-leaders.view');
        Gate::authorize('school-leaders.update');
        abort_unless($this->editingId !== null && $this->leadersQuery()->whereKey($this->editingId)->exists(), 404);
    }

    private function validateContent(): void
    {
        $this->validate([
            'profileEnabled' => ['boolean'],
            'profileHtmlEn' => ['nullable', 'string', 'max:100000'],
            'profileHtmlSi' => ['nullable', 'string', 'max:100000'],
            'profileCss' => ['nullable', 'string', 'max:50000'],
        ]);
    }

    /** @param array{html_en: string, html_si: string, css: string, compiled_css: string} $content */
    private function updatePreviews(array $content): void
    {
        $renderer = app(LeadershipProfileContent::class);
        $this->previewEn = $renderer->document($content['html_en'], $content['css'], $content['compiled_css'], 'en');
        $this->previewSi = $renderer->document($content['html_si'], $content['css'], $content['compiled_css'], 'si');
    }

    /** @return Builder<SchoolLeader> */
    private function leadersQuery(): Builder
    {
        return SchoolLeader::query()
            ->where('is_active', true)
            ->whereHas('position', static fn (Builder $query) => $query
                ->where('is_active', true))
            ->with('position')
            ->orderByRaw('CASE WHEN end_date IS NULL THEN 0 ELSE 1 END')
            ->orderBy('position_id')
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }
}
