<?php

namespace Modules\AppFiles\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Models\User;
use Modules\AppFiles\Support\FileManager;
use Modules\AppFiles\Support\OnlineMediaSearchService;

#[Title('Search Media Online')]
class PortalSearchOnline extends Component
{
    protected FileManager $files;

    public function boot(FileManager $files): void
    {
        $this->files = $files;
    }

    public function render(OnlineMediaSearchService $search): View
    {
        abort_unless($this->files->searchMediaOnlineEnabled($this->user()), 404);

        return view('appfiles::portal.search-online', [
            'providers' => $search->providers('all'),
            'uploadFromUrlEndpoint' => route('portal.files.picker-upload-from-url'),
            'searchOnlineEndpoint' => route('portal.files.picker-search-online'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Search Media Online'),
            'fullWorkspace' => true,
            'showLoadingBackdrop' => false,
        ]);
    }

    protected function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
