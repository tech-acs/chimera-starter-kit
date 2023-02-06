<?php

namespace Uneca\Chimera\Http\Livewire;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Uneca\Chimera\Jobs\ImportAreaSpreadsheetJob;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Notifications\TaskCompletedNotification;
use Uneca\Chimera\Notifications\TaskFailedNotification;
use Uneca\Chimera\Services\AreaTree;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;

class AreaSpreadsheetImporter extends Component
{
    use WithFileUploads;

    public $spreadsheet;
    public bool $fileAccepted = false;
    public array $areaLevels = [];
    public array $columnHeaders = [];
    public array $columnMapping = [];
    public string $filePath = '';
    public string $message = '';
    const CHUNK_SIZE = 4000;

    protected function rules()
    {
        $columnMappingRules = Arr::dot(
            collect($this->areaLevels)
                ->map(fn ($level) => "columnMapping.{$level}")
                ->mapWithKeys(function ($level) {
                    return [$level => [
                        'name' => 'required',
                        'code' => 'required',
                        'zeroPadding' => 'numeric|min:0'
                    ]];
                })
                ->all()
        );
        return array_merge(['spreadsheet' => 'required|file|mimes:csv'], $columnMappingRules);
    }

    protected function messages()
    {
        return Arr::dot(
            collect($this->areaLevels)
                ->map(fn ($level) => "columnMapping.{$level}")
                ->mapWithKeys(function ($level) {
                    return [$level => [
                        'name' => 'required',
                        'code' => 'required',
                        'zeroPadding' => 'invalid'
                    ]];
                })
                ->all()
        );
    }

    public function mount()
    {
        $this->areaLevels = (new AreaTree())->hierarchies;
        $this->columnMapping = AreaHierarchy::orderBy('index')->get()->mapWithKeys(function ($areaHierarchy) {
            return [$areaHierarchy->name => ['name' => '', 'code' => '', 'zeroPadding' => $areaHierarchy->zero_pad_length]];
        })->all();
    }

    public function updatedSpreadsheet()
    {
        $this->validateOnly('spreadsheet');
        $filename = collect([Str::random(40), $this->spreadsheet->getClientOriginalExtension()])->join('.');
        $this->spreadsheet->storeAs('/spreadsheets', $filename, 'imports');
        $this->filePath = Storage::disk('imports')->path('spreadsheets/' . $filename);
        $this->columnHeaders = SimpleExcelReader::create($this->filePath)->getHeaders();
        $this->fileAccepted = true;
    }

    /*private function makePathFormulaForExcel()
    {
        // TEXTJOIN(".", 0, TEXT(C2,"00"), TEXT(E2, "0000"), TEXT(L2, "0"))
        $paddedColumns = collect($this->columnMapping)->map(function ($column) {
            return 'TEXT(' . $column['code'] . ', "' . sprintf("%0{$column['zeroPadding']}s", 0) . '")';
        })->join(',');
        return '=TEXTJOIN(".", 0, ' . $paddedColumns . ')';
    }*/

    public function import()
    {
        $this->validate();

        $fileHandle = fopen($this->filePath, "r");
        $user = auth()->user();
        $jobs = [];
        $line = 0;
        $start = 0;
        $notProcessed = true;
        $areasTotalPreImport = Area::count();
        while (($fileLine = fgets($fileHandle)) !== false) {
            $line++;
            $notProcessed = true;
            if ($line % $this::CHUNK_SIZE === 0) {
                array_push(
                    $jobs,
                    new ImportAreaSpreadsheetJob($this->filePath, $start, $this::CHUNK_SIZE, $this->areaLevels, $this->columnMapping, $user, app()->getLocale())
                );
                $start = $line;
                $notProcessed = false;
            }
        }
        if ($notProcessed) {
            array_push(
                $jobs,
                new ImportAreaSpreadsheetJob($this->filePath, $start, $this::CHUNK_SIZE, $this->areaLevels, $this->columnMapping, $user, app()->getLocale())
            );
        }
        fclose($fileHandle);

        //$pathFormula = $this->makePathFormulaForExcel();
        Bus::chain(array_merge(
                $jobs,
                [
                    function () use ($line, $user, $areasTotalPreImport) {
                        $insertedCount = Area::count() - $areasTotalPreImport;
                        Notification::send($user, new TaskCompletedNotification(
                            'Task completed',
                            "$insertedCount areas have been imported."
                        ));
                    }
                ]
            ))
            ->catch(function (\Throwable $e) use ($user) {
                logger('ImportAreaSpreadsheet Job Failed', ['Exception: ' => $e->getMessage()]);
                Notification::send($user, new TaskFailedNotification(
                    'Error encountered importing areas',
                    $e->getMessage()
                ));
            })
            ->dispatch();

        $this->message = "The file is being imported. You will receive a notification when the process is complete.";
    }

    public function render()
    {
        return view('chimera::livewire.area-spreadsheet-importer');
    }
}
