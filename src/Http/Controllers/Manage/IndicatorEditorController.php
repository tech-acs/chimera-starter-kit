<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Services\DashboardComponentFactory;

class IndicatorEditorController extends Controller
{
    public function index(Indicator $indicator)
    {
        try {
            $instance = DashboardComponentFactory::makeIndicator($indicator);
            $filterPath = '';
            $data = $instance->getData($filterPath);
            if ($data->isEmpty()) {
                throw new \Exception("Indicator's getData() method must return some data before you can design the chart.");
            }

            $dataSources = toDataFrame($data);
            $dataSources->forget('path');

            return view('chimera::developer.indicator-editor.index', [
                'indicator' => $indicator,
                'dataSources' => $dataSources->toArray(),
                'data' => $indicator->data ?? [],
                'layout' => [...($indicator->layout ?? Chart::getDefaultLayout()), 'colorway' => ColorPalette::current()->colors],
                'config' => [...$instance->getConfig(), 'editable' => true],
            ]);

        } catch (\Throwable $e) {
            return redirect()->route('indicator.index')->withErrors(new MessageBag(['error' => $e->getMessage()]));
        }
    }

    public function edit(Indicator $indicator)
    {
        $filterPath = '';

        $instance = DashboardComponentFactory::makeIndicator($indicator);
        $data = $instance->getData($filterPath);
        $dataSources = toDataFrame($data);
        $dataSources->forget('path');

        return [
            'dataSources' => $dataSources->toArray(),
            'data' => $instance->getTraces($data, $filterPath, true),
            'layout' => $instance->getLayout($filterPath),
            'config' => [...$instance->getConfig(), 'editable' => true],
            'title' => $indicator->title ?? '',
        ];
    }

    public function update(Indicator $indicator, Request $request)
    {
        $traces = collect($request->json('data'));
        logger('received', ['traces' => $traces, 'layout' => $request->get('layout')]);
        $indicator->update(['data' => $traces, 'layout' => $request->get('layout')]);

        return response('Saved');
    }
}
