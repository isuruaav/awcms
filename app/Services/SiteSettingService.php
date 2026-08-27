<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class SiteSettingService
{
    /** @param array<string, mixed> $values */
    public function update(SiteSetting $settings, User $actor, array $values): SiteSetting
    {
        Gate::forUser($actor)->authorize('settings.manage');

        return DB::transaction(function () use ($settings, $actor, $values): SiteSetting {
            $locked = SiteSetting::query()->lockForUpdate()->findOrFail($settings->id);
            $old = $locked->only(array_keys($values));
            $values['updated_by'] = $actor->id;
            $locked->forceFill($values)->save();

            app(AuditLogger::class)->log(
                event: 'settings.updated',
                description: 'Website settings were updated.',
                actor: $actor,
                subject: $locked,
                oldValues: $old,
                newValues: $locked->only(array_keys($values)),
            );

            return $locked->refresh();
        }, 3);
    }
}
