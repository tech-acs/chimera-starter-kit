<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Jobs\BulkInvitationJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;

class BulkInviter extends Component
{
    use WithFileUploads;

    public bool $showBulkInviteForm = false;
    public $file;
    public $fileAccepted = false;
    public string $filePath = '';
    public bool $sendEmails = false;
    public bool $hasRoleColumn = false;

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
        $this->hasRoleColumn = in_array('role', $columnHeaders);
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
        BulkInvitationJob::dispatch($this->filePath, $this->hasRoleColumn, $this->sendEmails, auth()->user());
        $this->emit('processing');
    }

    public function render()
    {
        return view('chimera::livewire.bulk-inviter');
    }
}
