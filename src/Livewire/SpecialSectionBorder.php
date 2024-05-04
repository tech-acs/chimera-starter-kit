<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Component;

class SpecialSectionBorder extends Component
{
    public int $step = 7;
    public string $message = '';
    public bool $developerMode;

    public function mount()
    {
        $this->developerMode = session('developer_mode_enabled', false);
    }

    public function knock()
    {
        $this->step--;

        if ($this->step < 4) {
            $this->message = $this->step;
        }
        if ($this->step <= 0) {
            $this->message = 'Developer mode activated';
            $this->developerMode = true;
            session()->put('developer_mode_enabled', true);
        }
    }

    public function deactivate()
    {
        $this->developerMode = false;
        $this->reset('step', 'message');
        session()->put('developer_mode_enabled', false);
    }

    public function render()
    {
        return view('chimera::livewire.special-section-border');
    }
}
