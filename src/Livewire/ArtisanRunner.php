<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class ArtisanRunner extends Component
{
    public bool $modalOpen = false;

    public array $commands = [
        [
            'command' => 'optimize',
            'description' => 'Cache framework bootstrap, configuration, and metadata to increase performance',
        ],
        [
            'command' => 'optimize:clear',
            'description' => 'Remove the cached bootstrap files',
        ],
        [
            'command' => 'permission:cache-reset',
            'description' => 'Reset the permission cache',
        ],
        [
            'command' => 'migrate',
            'description' => 'Run the database migrations',
        ],
        [
            'command' => 'storage:link',
            'description' => 'Create the symbolic links configured for the application',
        ],
        [
            'command' => 'chimera:data-import',
            'description' => 'Restore postgres data (some tables) from file',
        ],
        /*[
            'command' => 'chimera:update',
            'description' => '',
        ],*/
    ];

    public function run(int $commandIndex)
    {
        $this->dispatch('happening', message: 'Command is running...');
        $command = $this->commands[$commandIndex]['command'];
        $result = Artisan::call($command);
        $output = str(Artisan::output())->trim()->trim('INFO')->value();
        $this->dispatch('happening', message: $output);
    }

    public function render()
    {
        return view('chimera::livewire.artisan-runner');
    }
}
