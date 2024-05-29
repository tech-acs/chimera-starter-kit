<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\DashboardComponentFactory;

class IndicatorEditorController extends Controller
{
    public function index(Indicator $indicator)
    {
        return view('chimera::developer.indicator-editor.index', compact('indicator'));
    }

    public function edit(Indicator $indicator)
    {
        $instance = DashboardComponentFactory::makeIndicator($indicator);
        $dataPlusAreaNames = $instance->addAreaNames($instance->getData([]), '');
        $dataSources = toDataFrame($dataPlusAreaNames);
        unset($dataSources['path']);
        return [
            'dataSources' => $dataSources,
            'data' => $instance->getDataForEditor(),
            'layout' => $instance->getLayoutForEditor(),
            'config' => $instance->getConfigForEditor(),
            'title' => $indicator->title ?? '',
        ];
    }

    public function update(Indicator $indicator, Request $request)
    {
        //throw new \Exception('Bad stuff happened!');
        $traces = collect($request->json('data'))
            ->map(function ($trace) {
                unset($trace['x'], $trace['y']);
                return $trace;
            });
        //logger('received', ['traces' => $traces[0]]);
        $indicator->update(['data' => $traces, 'layout' => $request->get('layout')]);
        return response('Saved');
    }
}
