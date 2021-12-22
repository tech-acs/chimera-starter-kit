<?php

namespace Uneca\Chimera\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Adminify extends Command
{
    protected $signature = 'adminify';

    protected $description = "Creates super admin user and assigns role 'Super Admin'";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);

        $email = $this->askValid(
            'What is the email address of the account you want to make a super admin?',
            'email',
            ['required', 'email']
        );
        $user = User::where('email', $email)->first();
        if ($user) {
            if ($user->hasRole('Super Admin')) {
                $this->info("The user account already has the 'Super Admin' role\n");
                return 0;
            }
        } else {
            $name = $this->askValid(
                'What is the name of the user you want to make a super admin?',
                'name',
                ['required', 'min:3']
            );
            $password = $this->askValid(
                'Please enter a password for the account',
                'password',
                ['required', 'min:8']
            );
            $user = User::firstOrCreate([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }
        $user->assignRole('Super Admin');
        $this->info("The user account with email address $email has been assigned the 'Super Admin' role\n");
    }

    protected function askValid($question, $field, $rules)
    {

        $value = $field == 'password' ? $this->secret($question) : $this->ask($question);
        if($message = $this->validateInput($rules, $field, $value)) {
            $this->error($message);
            return $this->askValid($question, $field, $rules);
        }
        return $value;
    }

    protected function validateInput($rules, $fieldName, $value)
    {
        $validator = Validator::make([
            $fieldName => $value
        ], [
            $fieldName => $rules
        ]);
        return $validator->fails()
            ? $validator->errors()->first($fieldName)
            : null;
    }
}
