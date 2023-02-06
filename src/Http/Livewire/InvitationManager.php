<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Mail\InvitationMail;
use Uneca\Chimera\Models\Invitation;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class InvitationManager extends Component
{
    use WithFileUploads;

    public $records = [];
    public $roles;
    public $showLink = false;
    public $link;
    public $email;
    public $role;
    public $sendEmail = true;
    public $showSingleInviteForm = false;
    public $showResult = false;
    public $resultTitle;
    public $resultBody;

    protected $rules = [
        'email' => 'required|email|unique:Uneca\Chimera\Models\Invitation,email|unique:Uneca\Chimera\Models\User,email',
    ];

    protected $messages = [
        'email.unique' => 'The email address is already in use',
    ];

    public function resendEmail(Invitation $invitation)
    {
        try {
            $this->sendEmail($invitation);
            $this->resultTitle = 'Email sent';
            $this->resultBody = "The invitation email has been resent to {$this->email}";
        } catch (\Exception $exception) {
            $this->resultTitle = 'Error occurred';
            $this->resultBody = "The invitation email was not sent. Please make sure mail sending has been properly configured. " .
                "Please refer to the error message below:<br><br>" .
                "<span style='color: #a93131;'>" . $exception->getMessage() . "</span>";
        }
        $this->showResult = true;
    }

    public function sendEmail(Invitation $invitation)
    {
        Mail::to($invitation->email)->send(new InvitationMail($invitation));
    }

    public function submit()
    {
        $this->validate();
        try {
            $expiresAt = now()->addHours(config('chimera.invitation.ttl_hours'));
            $invitation = Invitation::create([
                'email' => $this->email,
                'link' => URL::temporarySignedRoute('register', $expiresAt, ['email' => $this->email]),
                'expires_at' => $expiresAt,
                'role' => $this->role,
            ]);
            $this->loadData();
            $this->email = '';
            $this->role = '';
            $this->emit('invited');

            if ($this->sendEmail) {
                $this->sendEmail($invitation);
            }
        } catch (Exception $exception) {
            $this->addError('email', $exception->getMessage());
        }
    }

    public function renew(Invitation $invitation)
    {
        $expiresAt = now()->addHours(24);
        $invitation->update([
            'link' => URL::temporarySignedRoute('register', $expiresAt, ['email' => $invitation->email]),
            'expires_at' => $expiresAt
        ]);
        $this->loadData();
        $this->emit('renewed');
    }

    private function loadData()
    {
        $this->records = Invitation::all();
        $this->roles = Role::where('name', '!=', 'Super Admin')->get();
    }

    public function showLink(Invitation $invitation)
    {
        $this->link = $invitation->link;
        $this->showLink = true;
    }

    public function delete(Invitation $invitation)
    {
        $invitation->forceDelete();
        $this->loadData();
    }

    public function mount()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('chimera::livewire.invitation-manager');
    }
}
