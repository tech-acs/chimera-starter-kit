<?php

namespace App\Http\Livewire;

use App\Jobs\ImportTargetSpreadsheetJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;

class TargetSpreadsheetImporter extends Component
{
    use WithFileUploads;

    public $spreadsheet;
    public int $indicatorsToImport = 1;
    public array $columnHeaders = [];
    public array $columnMapping = [];
    public $filePath = '';
    public string $message = '';

    protected function rules()
    {
        $columnMappingRules = collect(Arr::dot(
            Collection::times($this->indicatorsToImport, function ($number) {
                return [
                    'name' => 'required',
                    'code' => 'required',
                ];
            })->all()
        ))->mapWithKeys(fn ($v, $k) => ["columnMapping.{$k}" => $v]);
        return [
            'spreadsheet' => 'required|file|mimetypes:text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ...$columnMappingRules
        ];
    }

    protected function messages()
    {
        $columnMappingMessages = collect(Arr::dot(
            Collection::times($this->indicatorsToImport, function ($number) {
                return [
                    'name' => 'required',
                    'code' => 'required',
                ];
            })->all()
        ))->mapWithKeys(fn ($v, $k) => ["columnMapping.{$k}" => $v])->all();
        return [
            'spreadsheet.mimetypes' => 'The file must be either an excel or a csv valid file',
            ...$columnMappingMessages
        ];
    }

    public function mount()
    {
        $this->columnMapping = Collection::times($this->indicatorsToImport, function () {
            return ['name' => '', 'code' => '', 'is_additive' => true];
        })->all();
    }

    public function updatedSpreadsheet()
    {
        $this->validateOnly('spreadsheet');
        $filename = collect([Str::random(40), $this->spreadsheet->getClientOriginalExtension()])->join('.');
        $this->spreadsheet->storeAs('/spreadsheets', $filename, 'imports');
        $this->filePath = Storage::disk('imports')->path('spreadsheets/' . $filename);
        $this->columnHeaders = SimpleExcelReader::create($this->filePath)->getHeaders();
    }

    public function add()
    {
        $this->indicatorsToImport++;
        $this->columnMapping[] = ['name' => '', 'code' => '', 'is_additive' => true];
    }

    public function import()
    {
        $this->validate();

        ImportTargetSpreadsheetJob::dispatch($this->filePath, $this->areaLevels, $this->columnMapping, auth()->user());
        $this->message = "The file is being imported. You will receive a notification when the process is complete.";
    }

    public function render()
    {
        return view('livewire.target-spreadsheet-importer');
    }
}
