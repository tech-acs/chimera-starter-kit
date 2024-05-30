<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\AreaResolver;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Traits\FilterBasedAxisTitle;

abstract class Chart extends Component
{
    use FilterBasedAxisTitle;

    public Indicator $indicator;
    public string $graphDiv;
    public array $data = [];
    public array $layout = [];
    public array $config = [];

    public bool $isBeingFeatured = false;
    public bool $linkedFromScorecard = false;
    public bool $useDynamicAreaXAxisTitles = false;
    public array $aggregateAppendedTraces = []; // ['trace name' => 'avg'] ... sum, count, min, max, mode, median

    const DEFAULT_CONFIG = [
        'responsive' => true,
        'displaylogo' => false,
        'modeBarButtonsToRemove' => ['select2d', 'lasso2d', 'autoScale2d', 'hoverClosestCartesian', 'hoverCompareCartesian'],
    ];
    const DEFAULT_LAYOUT = [
        //'height' => 450,
        'title' => [],
        'showlegend' => true,
        'legend' => ['orientation' => 'h', 'x' => 0, 'y' => 1.12],
        'xaxis' => [
            'type' => 'category',
            'tickmode' => 'linear',
            'automargin' => true,
            'title' => ['text' => ''],
        ],
        'yaxis' => [
            'title' => ['text' => ''],
        ],
        'margin' => ['l' => 60, 'r' => 10, 't' => 10, 'b' => 40],
        'modebar' => ['orientation' => 'v', 'color' => 'white', 'bgcolor' => 'darkgray'],
        'dragmode' => 'pan',
    ];

    public function placeholder()
    {
        return <<<'HTML'
            <div class="flex flex-col absolute inset-0 justify-center items-center z-10 opacity-80 bg-white">
                <svg class="block animate-spin size-10 mb-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Initializing...
            </div>
        HTML;
    }

    #[On(['updateRequest.{indicator.id}', 'filterChanged'])]
    public function updateChart(AreaResolver $areaResolver)
    {
        /*$filterPath = AreaTree::getFinestResolutionFilterPath([...auth()->user()->areaRestrictionAsFilter(), ...session()->get('area-filter', []) ]);
        $filter = AreaTree::pathAsFilter($filterPath);*/

        $this->data = $this->getTraces($this->getData($areaResolver->expandedPath), $areaResolver->path);
        $this->layout = $this->getLayout($areaResolver->path);

        $this->dispatch("updateResponse.{$this->indicator->id}", $this->data, $this->layout);
    }

    public function getConfig(): array
    {
        return [
            ...self::DEFAULT_CONFIG,
            'toImageButtonOptions' => ['filename' => $this->graphDiv . ' (' . now()->toDayDateTimeString() . ')'],
            'locale' => app()->getLocale(),
        ];
    }

    public function getData(array $filter): Collection
    {
        return $this->indicator->data;
    }

    public function addAreaNames(Collection $data, string $filterPath, string $keyByColumn = 'area_code'): Collection
    {
        if ($data->isEmpty()) {
            return $data;
        }
        $areas = (new AreaTree())->areas($filterPath);
        $dataKeyByAreaCode = $data->keyBy($keyByColumn);
        $columns = array_keys((array) $data[0]);
        return $areas->map(function ($area) use ($dataKeyByAreaCode, $columns, $keyByColumn) {
            foreach ($columns as $column) {
                if ($column != $keyByColumn) {
                    $area->{$column} = $dataKeyByAreaCode[$area->code]->{$column} ?? 0;
                }
            }
            return $area;
        });
    }

    protected function getTraces(Collection $data, string $filterPath): array
    {
        $traces = $this->indicator->data;
        $data = toDataFrame($this->addAreaNames($data, $filterPath));
        foreach ($traces as $index => $trace) {
            $columnNames = Arr::get($traces[$index], 'meta.columnNames', null);
            if ($columnNames) {
                $traces[$index]['x'] = $data[$columnNames['x']] ?? null;
                $traces[$index]['y'] = $data[$columnNames['y']] ?? null;
            }
            if (in_array($trace['name'], array_keys($this->aggregateAppendedTraces))) {
                $aggOp = $this->aggregateAppendedTraces[$trace['name']];
                array_push($traces[$index]['x'], __('All') . ' ' . $this->getAreaBasedAxisTitle($filterPath));
                array_push($traces[$index]['y'], collect($traces[$index]['y'])->filter()->{$aggOp}());
            }
        }
        return $traces;
    }

    protected function getLayout(string $filterPath): array
    {
        $layout = $this->indicator->layout;
        if ($this->useDynamicAreaXAxisTitles) {
            $layout['xaxis']['title']['text'] = $this->getAreaBasedAxisTitle($filterPath, true);
        }
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        return [...$layout, 'colorway' => $currentPalette->colors];
    }

    public function mount()
    {
        $this->graphDiv = $this->indicator->id;
        $this->config = $this->getConfig();

        // ToDo: call property validator
    }

    public function render()
    {
        return view('chimera::livewire.chart');
    }




    protected function isDataEmpty(): bool
    {
        return array_reduce($this->data, function ($carry, $trace) {
            /*
            * Not all graphs put their data under the 'x' and 'y' keys.
            * Pies for example put it in the 'values' key.
            * Therefore, you might need to add those other cases here!
            */
            return match ($trace['type'] ?? null) {
                'pie' => empty($trace['values']) && $carry,
                default => empty($trace['x']) && $carry,
            };
        }, true);
    }

    public static function getDefaultLayout(): array
    {
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        //return [...self::DEFAULT_LAYOUT, 'colorway' => $currentPalette->colors];
        return [
            ...Storage::disk("plotly_defaults")->json("layout.json"),
            'colorway' => $currentPalette->colors
        ];
    }

    public function getConfigForEditor(): array
    {
        return [...$this->getConfig(), 'editable' => true];
    }

    public function getDataForEditor(): array
    {
        return $this->getTraces($this->getData([]), '');
    }

    public function getLayoutForEditor(): array
    {
        return $this->getLayout('');
    }

}
