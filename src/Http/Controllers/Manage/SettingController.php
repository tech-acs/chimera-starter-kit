<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Uneca\Chimera\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $groupedSettings = Setting::directlyEditable()
            ->orderBy('id')
            ->get()
            ->map(function ($setting) {
                $setting->value = Crypt::decryptString($setting->value);
                list($label, $help) = str($setting->label)->explode('|');
                $setting->label = $label;
                $setting->help = $help;
                return $setting;
            })
            ->groupBy('group');
        return view('chimera::setting.index', compact('groupedSettings'));
    }

    public function update(Request $request)
    {
        $checkboxTypes = Setting::directlyEditable()
            ->where('input_type', 'checkbox')
            ->pluck('value', 'key')
            ->map(fn($v) => null)
            ->toArray();
        $request->mergeIfMissing($checkboxTypes);
        $inputData = $request->except('_token');
        foreach ($inputData as $key => $value) {
            // ToDo: validate. But how?
            Setting::where('key', $key)->update(['value' => Crypt::encryptString($value)]);
        }
        Cache::forget('settings');
        session()->flash('active-tab', $request->get('active-tab', 'group1'));

        return redirect()->route('setting.edit')
            ->with(['message' => 'Successfully updated the settings.']);
    }
}
