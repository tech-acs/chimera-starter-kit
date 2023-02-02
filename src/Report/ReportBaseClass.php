<?php

namespace Uneca\Chimera\Report;

use Illuminate\Support\Facades\Notification;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\Report;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Uneca\Chimera\Notifications\ReportGeneratedNotification;
use Uneca\Chimera\Services\AreaTree;

abstract class ReportBaseClass
{
    public Report $report;
    public $fileType = 'csv';

    public function __construct(Report $report)
    {
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
        SimpleExcelWriter::create(Storage::disk('reports')
            ->path($filename))
            ->addHeader([$this->report->title])
            ->addHeader(["As of " . now()->toDayDateTimeString()])
            ->addHeader(array_keys(reset($data))) // Actual headers (get from the first row)
            ->addRows($data);
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
        $this->report->update(['last_generated_at' => now()]);

        Notification::send($this->report->users, new ReportGeneratedNotification($this->report));
    }
}
