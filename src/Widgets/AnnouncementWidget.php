<?php

namespace Thoughtco\StatamicAgency\Widgets;

use Illuminate\View\View;
use Statamic\Statamic;
use Statamic\Widgets\Widget;

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

        return view('statamic-agency::widgets.announcement_widget');
    }
}
