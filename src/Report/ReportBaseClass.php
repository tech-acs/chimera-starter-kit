<?php

namespace Uneca\Chimera\Report;

use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\Report;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Uneca\Chimera\Services\AreaTree;

abstract class ReportBaseClass
{
    public Report $report;
    public $fileType = 'csv';

    public function __construct(Report $report)
    {
        //$classPath = "App\Reports\\" . str($report->name)->replace('/', '\\');
        $this->report = $report;
    }

    abstract public function getData(array $filter): Collection;

    public function filename(array $filter): string
    {
        $suffix = implode('-', $filter);
        return "{$this->report->slug}$suffix.{$this->fileType}";
    }

    protected function writeFile(array $data, string $filename)
    {
        $writer = SimpleExcelWriter::create(Storage::disk('reports')->path($filename))->addRows($data);
    }

    protected function generateForFilter(array $filter)
    {
        $data = $this->getData($filter);
        if (empty($data)) {
            throw new Exception('There is no data to export');
        }
        $rowified = $data->map(function ($obj) {
            return (array)$obj;
        })->all();
        $this->writeFile($rowified, $this->filename($filter));
    }

    public function generate()
    {
        // Get all user area restrictions and loop them as filter, including [] for non-restricted users
        $paths = AreaRestriction::distinct('path')->pluck('path');
        $this->generateForFilter([]);
        foreach ($paths as $path) {
            $filter = AreaTree::pathAsFilter($path);
            $this->generateForFilter($filter);
        }
        /*$filter = [];
        $data = $this->getData($filter);
        if (empty($data)) {
            throw new Exception('There is no data to export');
        }
        $rowified = $data->map(function ($obj) {
            return (array)$obj;
        })->all();
        $this->writeFile($rowified);*/

        $this->report->update(['last_generated_at' => now()]);
    }
}
