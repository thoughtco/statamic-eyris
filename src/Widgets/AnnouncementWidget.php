<?php

namespace Thoughtco\Eyris\Widgets;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Statamic\Statamic;
use Statamic\Widgets\Widget;
use Thoughtco\Eyris\Facades\Agent;

class AnnouncementWidget extends Widget
{
    public static $handle = 'eyris-announcements';

    public bool $supportsStatamicUiLibrary = false;

    public function __construct()
    {
        $this->supportsStatamicUiLibrary = substr(Statamic::version(), 0, 1) >= 6;
    }

    public function html(): string|View
    {
        if (! $this->supportsStatamicUiLibrary) {
            return '<div class="card p-0 content">Statamic v6.0 or higher is required to use this widget.</div>';
        }

        $announcements = Cache::remember($this::$handle.'::announcements', 3600, function () {
            return Agent::getAnnouncements();
        });

        return view('statamic-eyris::widgets.announcement_widget', [
            'slides' => $announcements,
        ]);
    }
}
