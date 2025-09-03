<?php

namespace Thoughtco\StatamicAgency\Commands;

use Illuminate\Console\Command;
use Thoughtco\StatamicAgency\Facades\Agency;

class UpdateEnvironment extends Command
{
    protected $signature = 'agency:update-environment';

    protected $description = 'Update Agency with the environment settings for this site';

    public function handle()
    {
        Agency::updateEnvironment();

        $this->info('Agency has been updated');
    }
}
