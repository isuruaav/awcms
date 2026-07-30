<?php

namespace App\Livewire\Admin\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class PageEdit extends Component
{
    public function render(): View
    {
        return view(
            'livewire.admin.pages.page-edit',
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit Page',
            ],
        );
    }
}
