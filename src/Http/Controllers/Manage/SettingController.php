<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::directlyEditable()->orderBy('id')->get(['key', 'value', 'label']);
        return view('chimera::setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $inputData = $request->except('_token');
        foreach ($inputData as $key => $value) {
            // ToDo: validate
            Setting::where('key', $key)->update(['value' => $value]);
        }
        Cache::forget('settings');
        session()->put('tab', $request->get('tab', 'ownership'));
        return redirect()->route('setting.edit')
            ->with(['message' => 'Successfully updated the settings.']);
    }
}
