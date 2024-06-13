<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Features\SupportStreaming\HandlesStreaming;
use Uneca\Chimera\Jobs\BulkInvitationJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

class BulkInviter extends Component
{
    use WithFileUploads;

    public bool $showBulkInviteForm = false;
    public $file;
    public $fileAccepted = false;
    public string $filePath = '';
    public bool $sendEmails = false;

    protected $rules = [
        'file' => 'required|file|mimes:csv,xlsx'
    ];

    protected $listeners = [ 'pleaseHideForm' => 'hideForm'];

    public function updatedFile()
    {
        $this->fileAccepted = false;
        $this->validateOnly('file');
        $filename = collect([Str::random(40), $this->file->getClientOriginalExtension()])->join('.');
        $this->file->storeAs('/spreadsheets', $filename, 'imports');
        $this->filePath = Storage::disk('imports')->path('spreadsheets/' . $filename);
        $columnHeaders = SimpleExcelReader::create($this->filePath)->getHeaders();
        if (! in_array('email', $columnHeaders)) {
            throw ValidationException::withMessages([
                'file' => ["The file does not seem to have an 'email' column"],
            ]);
        }
        $this->fileAccepted = true;
    }

    public function resetForm()
    {
        $this->fileAccepted = false;
        $this->filePath = '';
        $this->file = null;
        $this->sendEmails = false;
    }

    public function hideForm()
    {
        $this->showBulkInviteForm = false;
        $this->resetForm();
    }

    public function invite()
    {
        $this->validate();
        BulkInvitationJob::dispatch($this->filePath, $this->sendEmails, auth()->user());
        $this->dispatch('processing');
    }

    public function downloadTemplate()
    {
        $pathToCsv = Storage::disk('local')->path('bulk_invitations_template.xlsx');
        SimpleExcelWriter::create($pathToCsv)->addHeader(['email', 'role']);
        return response()->download($pathToCsv);
    }

    public function render()
    {
        return view('chimera::livewire.bulk-inviter');
    }
}
