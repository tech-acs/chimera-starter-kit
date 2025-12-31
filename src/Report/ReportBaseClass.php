<?php

namespace Uneca\Chimera\Report;

use Illuminate\Support\Facades\Notification;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\Report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Uneca\Chimera\Notifications\ReportGeneratedNotification;
use Uneca\Chimera\Services\AreaTree;

use OpenSpout\Common\Entity\Style\{Style, Border, BorderPart, CellAlignment};

abstract class ReportBaseClass
{
    public Report $report;

    public $fileType = 'xlsx';

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    abstract public function getData(string $filterPath): Collection;

    public function filename(string $path): string
    {
        $filter = AreaTree::pathAsFilter($path);
        $suffix = implode('-', $filter);
        return "{$this->report->slug}$suffix.{$this->fileType}";
    }

    protected function writeFile(array $data, string $filename)
    {
        if (empty($data)) {
            $data = [['No data found.' => '']];
        }
        $border = new Border(
            new BorderPart(Border::BOTTOM, width: Border::WIDTH_THIN),
            new BorderPart(Border::LEFT, width: Border::WIDTH_THIN),
            new BorderPart(Border::RIGHT, width: Border::WIDTH_THIN),
            new BorderPart(Border::TOP, width: Border::WIDTH_THIN)
        );
        $style = (new Style())->setBorder($border);

        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setShouldWrapText()
            ->setBackgroundColor('d1d5db')
            ->setBorder($border);
        $columnHeaderStyle = $headerStyle->setCellAlignment(CellAlignment::CENTER);
        $columnHeaders = array_keys(reset($data)); // Actual headers (get from the first row)

        SimpleExcelWriter::create(
            Storage::disk('reports')->path($filename),
            configureWriter: function ($writer) use ($columnHeaders) {
                $options = $writer->getOptions();
                $options->DEFAULT_COLUMN_WIDTH = 15;
                $options->DEFAULT_ROW_HEIGHT = 18;
                // Arguments: startColumn, startRow, endColumn, endRow
                $options->mergeCells(0, 1, count($columnHeaders) - 1, 1);
                $options->mergeCells(0, 2, count($columnHeaders) - 1, 2);
            }
        )
        ->noHeaderRow()
        ->addRow([$this->report->title], $headerStyle)
        ->addRow([__("As of ") . now()->toDayDateTimeString()], $headerStyle)
        ->setHeaderStyle($columnHeaderStyle)
        ->addHeader($columnHeaders)
        ->addRows($data, $style);

        $this->fileType = 'xlsx';
    }

    protected function writeCsvFile(array $data, string $filename)
    {
        if (empty($data)) {
            $data = [['No data found.' => '']];
        }
        SimpleExcelWriter::create(Storage::disk('reports')
            ->path($filename))
            ->addHeader([$this->report->title])
            ->addHeader(["As of " . now()->toDayDateTimeString()])
            ->addHeader(array_keys(reset($data))) // Actual headers (get from the first row)
            ->addRows($data);
        $this->fileType = 'csv';
    }

    protected function generateForPath(string $path)
    {
        $data = $this->getData($path);
        /*if ($data->isEmpty()) {
            //dump("There is no data to export for path: $path");
            return;
        }*/
        $rowified = $data->map(function ($obj) {
            return (array)$obj;
        })->all();
        $this->writeFile($rowified, $this->filename($path));
    }

    public function generate()
    {
        // Get all user area restrictions and loop them as filter, including [] for non-restricted users
        $paths = AreaRestriction::distinct('path')->pluck('path');
        $this->generateForPath(''); // '' means no restriction or national level view
        foreach ($paths as $path) {
            //$filter = AreaTree::pathAsFilter($path);
            $this->generateForPath($path);
        }
        $this->report->update(['last_generated_at' => now()]);

        try {
            Notification::send($this->report->users, new ReportGeneratedNotification($this->report));
        } catch (\Exception $exception) {
            logger('Trying to notify', ['Exception' => $exception->getMessage()]);
        }
    }
}
