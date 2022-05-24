<?php

namespace App\Http\Livewire;

use App\Models\Invitation;
use Exception;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class InvitationManager extends Component
{
    public $records = [];
    public $roles;
    public $showModal = false;
    public $showLink = false;
    public $link;
    public $email;
    public $role;

    protected $rules = [
        'email' => 'required|email|unique:App\Models\Invitation,email|unique:App\Models\User,email',
    ];

    protected $messages = [
        'email.unique' => 'The email address is already in use',
    ];

    public function submit()
    {
        $this->validate();
        try {
            $expiresAt = now()->addHours(config('chimera.invitation.ttl_hours'));
            Invitation::create([
                'email' => $this->email,
                'link' => URL::temporarySignedRoute('register', $expiresAt, ['email' => $this->email]),
                'expires_at' => $expiresAt,
                'role' => $this->role,
            ]);
            $this->loadData();
            $this->email = '';
            $this->role = '';
            $this->emit('invited');
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
        //$invitation->update(['status' => 'Deleted']);
        $invitation->forceDelete();
        $this->loadData();
    }

    public function mount()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.invitation-manager');
    }
}
