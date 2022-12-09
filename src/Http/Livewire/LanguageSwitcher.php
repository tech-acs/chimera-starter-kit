<?php

namespace Uneca\Chimera\Http\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $locale;
    public array $languages;
    public string $route;

    public function mount()
    {
        $this->locale = Cookie::get('locale', 'en');
        $this->route = url()->current();
        $this->languages = config('languages');
    }

    public function changeHandler($lang)
    {
        Cookie::queue('locale', $lang);
        App::setLocale($lang);
        $this->locale = app()->getLocale();
        return redirect($this->route);
    }

    public function render()
    {
        return view('chimera::livewire.language-switcher');
    }
}
