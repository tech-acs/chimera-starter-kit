<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;

class DataImport extends Command
{
    protected $signature = 'chimera:data-import';

    protected $description = 'Import production destined data from file';

    public function handle()
    {
        return Command::SUCCESS;
    }
}
