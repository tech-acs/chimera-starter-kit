<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use Uneca\Chimera\Enums\DataStatus;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Traits\AreaResolver;
use Uneca\Chimera\Traits\Cachable;
use Uneca\Chimera\Traits\FilterBasedAxisTitle;
use Uneca\Chimera\Traits\PlotlyDefaults;

abstract class Chart extends Component
{
    use Cachable;
    use AreaResolver;
    use PlotlyDefaults;
    use FilterBasedAxisTitle;

    public Indicator $indicator;
    public string $graphDiv;
    public array $data = [];
    public array $layout = [];
    public array $config = [];
    public Carbon $dataTimestamp;

    public bool $isBeingFeatured = false;
    public bool $useDynamicAreaXAxisTitles = false;
    public array $aggregateAppendedTraces = []; // ['trace name' => 'avg'] ... sum, count, min, max, mode, median

    public function mount()
    {
        $this->graphDiv = $this->indicator->id;
        $this->config = $this->getConfig();
        list($this->filterPath,) = $this->areaResolver();
        $this->checkData();
        // ToDo: call property validator for $aggregateAppendedTraces
    }

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

    public function cacheKey(): string
    {
        return implode(':', ['indicator', $this->indicator->id, $this->filterPath]);
    }

    #[On(['filterChanged'])]
    public function update()
    {
        list($this->filterPath,) = $this->areaResolver();
        $this->checkData();
    }

    #[On(['dataReady'])]
    public function sendUpdates()
    {
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

    public function getTraces(Collection $data, string $filterPath, bool $designMode = false): array
    {
        $traces = $this->indicator->data;
        $data = toDataFrame($data);
        if ($data->isNotEmpty()) {
            $newDynamicTraces = [];
            foreach ($traces as $index => $trace) {
                $columnNames = Arr::get($trace, 'meta.columnNames');
                if ($columnNames) {
                    /*$traces[$index]['x'] = $data[$columnNames['x']] ?? null;
                    $traces[$index]['y'] = $data[$columnNames['y']] ?? null;*/
                    foreach ($columnNames as $key => $columnName) {
                        $traces[$index][$key] = $data[$columnName] ?? null;
                    }
                }
                $traceName = strip_tags($trace['name'] ?? '');
                if ((! $designMode) && in_array($traceName, array_keys($this->aggregateAppendedTraces))) {
                    $aggOp = $this->aggregateAppendedTraces[$traceName];
                    if (strtolower($aggOp) === 'sum') {
                        $newDynamicTrace = [
                            ...$traces[$index],
                            'name' => $traceName . __(' (across ') . strtolower($this->getAreaBasedAxisTitle($filterPath)) . ')',
                            'yaxis' => 'y2',
                            'showlegend' => false,
                            'x' => [__('<b>All') . ' ' . $this->getAreaBasedAxisTitle($filterPath) . '</b>'],
                            'y' => [collect($traces[$index]['y'])->{$aggOp}()],
                            'hovertemplate' => '%{y:.0f}'
                        ];
                        unset($newDynamicTrace['meta'], $newDynamicTrace['xsrc'], $newDynamicTrace['ysrc']);
                        $traces[] = $newDynamicTrace;
                    } else {
                        $traces[$index]['x'][] = __('<b>All').' '.$this->getAreaBasedAxisTitle($filterPath).'</b>';
                        $traces[$index]['y'][] = collect($traces[$index]['y'])->{$aggOp}();
                    }
                }
            }
            foreach ($newDynamicTraces as $newDynamicTrace) {
                $traces[] = $newDynamicTrace;
            }
        } else {
            $traces = [];
        }
        return $traces;
    }

    public function getLayout(string $filterPath): array
    {
        $layout = $this->indicator->layout;
        if ($this->useDynamicAreaXAxisTitles) {
            $layout['xaxis']['title']['text'] = $this->getAreaBasedAxisTitle($filterPath, true);
        }
        if (! empty($this->aggregateAppendedTraces)) {
            $layout['yaxis2']['side'] = 'right';
            $layout['yaxis2']['overlaying'] = 'y';
            $layout['yaxis2']['showgrid'] = false;
        }
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        return [...$layout, 'colorway' => $currentPalette->colors];
    }

    public static function getDefaultLayout(): array
    {
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        return [
            ...self::DEFAULT_LAYOUT,
            'colorway' => $currentPalette->colors
        ];
    }

    public function setPropertiesFromData(): void
    {
        list($this->dataTimestamp, $data) = Cache::get($this->cacheKey());
        $this->data = $this->getTraces($data, $this->filterPath);
        $this->layout = $this->getLayout($this->filterPath);
        $this->dataStatus = empty($this->data) ?
            DataStatus::EMPTY->value :
            DataStatus::RENDERABLE->value;
    }

    public function getDataModel(): Model
    {
        return $this->indicator;
    }

    public function render()
    {
        return view('chimera::livewire.chart');
    }
}
