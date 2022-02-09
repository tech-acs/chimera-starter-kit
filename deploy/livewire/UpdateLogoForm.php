<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateLogoForm extends Component
{
    use WithFileUploads;

    public $state = [];

    public $logo;

    public function mount()
    {
        $this->state = Auth::user()->withoutRelations()->toArray();
    }

    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        /*$this->resetErrorBag();

        $updater->update(
            Auth::user(),
            $this->logo
                ? array_merge($this->state, ['photo' => $this->logo])
                : $this->state
        );

        if (isset($this->logo)) {
            return redirect()->route('profile.show');
        }

        $this->emit('saved');

        $this->emit('refresh-navigation-menu');*/
    }

    public function deleteLogo()
    {
        //Auth::user()->deleteProfilePhoto();
    }

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function render()
    {
        return view('setting.update-logo-form');
    }
}
