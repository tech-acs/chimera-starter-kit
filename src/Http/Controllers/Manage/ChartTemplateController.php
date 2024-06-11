<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Models\ChartTemplate;

class ChartTemplateController extends Controller
{
    private function columnInfoForHumans(array $traceColumns): string
    {
        try {
            return collect($traceColumns)
                ->map(fn (array $columns, string $traceName) => "<b>$traceName trace</b><br />" .
                    collect($columns)
                        ->filter(fn ($column, $axis) => in_array($axis, ['x', 'y', 'z', 'text']))
                        ->map(fn ($column, $axis) => "$axis: $column")
                        ->join(', '))->join('<br />');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function index()
    {
        $records = ChartTemplate::all();
        $records->map(function ($template) {
            $traceColumns = [];
            foreach ($template->data as $trace) {
                $traceColumns[$trace['name']] = $trace['meta']['columnNames'];
            }
            $template->columns = $this->columnInfoForHumans($traceColumns);
            return $template;
        });
        return view('chimera::chart-template.index', compact('records'));
    }

    public function store(Request $request)
    {
        //logger('received', ['received stuff' => $request->only(['name', 'category', 'description', 'data', 'layout'])]);
        ChartTemplate::create($request->only(['name', 'category', 'description', 'data', 'layout']));
        return response('Saved');
    }

    public function destroy(ChartTemplate $chartTemplate)
    {
        $chartTemplate->delete();
        return redirect()->route('chart-template.index')->withMessage('Chart template deleted');
    }
}
