<?php

namespace Thoughtco\Eyris\Commands;

use Illuminate\Console\Command;
use Thoughtco\Eyris\Facades\Agent;

class UpdateEnvironment extends Command
{
    protected $signature = 'eyris:update-environment';

    protected $description = 'Update Eyris with the environment settings for this site';

    public function handle()
    {
        Agent::updateEnvironment();

        $this->info('Eyris has been updated');
    }
}
