<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pageCount' => Page::query()->count(),
            'newsCount' => News::query()->count(),
            'galleryCount' => Gallery::query()->count(),
            'documentCount' => Document::query()->count(),
            'newContactCount' => ContactMessage::query()->where('status', 'new')->count(),
            'recentActivity' => AuditLog::query()->latest('created_at')->limit(8)->get(),
        ]);
    }
}
