<?php

namespace Thoughtco\StatamicAgency\Widgets;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Statamic\Statamic;
use Statamic\Widgets\Widget;
use Thoughtco\StatamicAgency\Facades\Agency;

class AnnouncementWidget extends Widget
{
    public static $handle = 'penfold_announcement';

    public bool $supportsStatamicUiLibrary = false;

    public function __construct() {
        $this->supportsStatamicUiLibrary = substr(Statamic::version(), 0, 1) >= 6;
    }

    public function html(): string|View
    {
        if (! $this->supportsStatamicUiLibrary) {
            return '<div class="card p-0 content">Statamic v6.0 or higher is required to use this widget.</div>';
        }

        $announcements = Cache::remember($this::$handle.'::announcements', 3600, function () {
            return Agency::getAnnouncements();
        });

        return view('statamic-agency::widgets.announcement_widget', [
            'slides' => $announcements,
        ]);
    }
}
