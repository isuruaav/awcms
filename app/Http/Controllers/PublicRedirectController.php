<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicRedirectController
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('redirects')) {
            throw new NotFoundHttpException;
        }

        $path = '/'.ltrim($request->path(), '/');
        $redirect = Redirect::query()->active()->where('source_path', $path)->first();

        if (! $redirect instanceof Redirect) {
            throw new NotFoundHttpException;
        }

        $redirect->increment('hit_count');
        $redirect->forceFill(['last_hit_at' => now()])->saveQuietly();

        return redirect()->to($redirect->destination_url, $redirect->http_status);
    }
}
