<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

abstract class ReportBlueprint
{
    public $connection;
    public $report;
    public $file;
    public $fileType = 'csv';

    public function __construct(Report $report, ?string $connection = null)
    {
        $this->connection = $connection;
        $this->report = $report;
        $this->file = "{$report->slug}.{$this->fileType}";
    }

    abstract public function getCollection();

    public function writeFile($data)
    {
        $writer = SimpleExcelWriter::create(Storage::disk('reports')->path($this->file))
            ->addRows($data);
    }

    public function download()
    {
        return Storage::disk('reports')->download($this->file);
    }

    public function generate()
    {
        $data = $this->getCollection();
        $rowified = $data->map(function ($obj) {
            return (array)$obj;
        })->all();
        $this->writeFile($rowified);

        $this->report->update(['last_generated_at' => now()]);
    }
}
