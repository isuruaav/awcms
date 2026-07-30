<?php

namespace App\Livewire\Admin\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class PageCreate extends Component
{
    public function render(): View
    {
        return view(
            'livewire.admin.pages.page-create',
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Create Page',
            ],
        );
    }
}
