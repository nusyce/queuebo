<?php

namespace App\Livewire\Portal;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User Dashboard')]
class Dashboard extends Component
{
    /**
     * @param  array<int, string>  $itemIds
     */
    public function saveLayout(array $itemIds): void
    {
        $payload = Validator::make([
            'item_ids' => $itemIds,
        ], [
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['string'],
        ])->validate();

        save_user_dashboard_layout(auth()->user(), $payload['item_ids']);
    }

    public function render(): View
    {
        return view(theme_view('livewire.portal.dashboard', 'app'), [
            'welcomeItems' => user_dashboard_items(auth()->user(), 'welcome'),
            'dashboardItems' => user_dashboard_items(auth()->user(), 'main'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('User Dashboard'),
        ]);
    }
}
