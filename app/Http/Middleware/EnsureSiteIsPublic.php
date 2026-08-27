<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSiteIsPublic
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*', 'login', 'logout', 'settings*', 'forgot-password', 'reset-password*', 'two-factor-challenge', 'verify-email*', '.well-known/*', 'up')) {
            return $next($request);
        }

        if (! Schema::hasTable('site_settings')) {
            return $next($request);
        }

        $settings = SiteSetting::query()->first();

        if (! $settings instanceof SiteSetting || ! $settings->maintenance_mode) {
            return $next($request);
        }

        $user = $request->user();
        if ($user instanceof User && $user->can('admin.access')) {
            return $next($request);
        }

        return response()->view('public.maintenance', ['settings' => $settings], 503);
    }
}
