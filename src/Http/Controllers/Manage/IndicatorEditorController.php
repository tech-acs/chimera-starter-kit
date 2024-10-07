<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\DashboardComponentFactory;

class IndicatorEditorController extends Controller
{
    public function index(Indicator $indicator)
    {
        try {
            $instance = DashboardComponentFactory::makeIndicator($indicator);
            $data = $instance->getData('');
            if ($data->isEmpty()) {
                throw new \Exception("Indicator's getData() method must return some data before you can design the chart.");
            }
            $defaultLayout = json_encode(Chart::getDefaultLayout());
            return view('chimera::developer.indicator-editor.index', compact('indicator', 'defaultLayout'));

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
        unset($dataSources['path']);
        return [
            'dataSources' => $dataSources,
            'data' => $instance->getTraces($data, $filterPath, true),
            'layout' => $instance->getLayout($filterPath),
            'config' => [...$instance->getConfig(), 'editable' => true],
            'title' => $indicator->title ?? '',
        ];
    }

    public function update(Indicator $indicator, Request $request)
    {
        //throw new \Exception('Bad stuff happened!');

        $traces = collect($request->json('data'));
        logger('received', ['traces' => $traces, 'layout' => $request->get('layout')]);
        $indicator->update(['data' => $traces, 'layout' => $request->get('layout')]);
        return response('Saved');
    }
}
