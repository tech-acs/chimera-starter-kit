<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class Adminify extends Command
{
    protected $signature = 'adminify';

    protected $description = "Creates super admin user and assigns role 'Super Admin'";

    public const ROLE = 'Super Admin';

    public function __construct()
    {
        parent::__construct();
    }

    private function permissionsEnabled()
    {
        return class_exists(Role::class);
    }

    public function handle()
    {
        $this->newLine();
        $this->line('This command will create/retrieve the user account and will assign it the ' . self::ROLE . ' role');

        $email = $this->askValid(
            "What is the email address of the account you want to assign the role?",
            'email',
            ['required', 'email']
        );
        $name = $this->askValid(
            'What is the name of the user?',
            'name',
            ['required', 'min:3']
        );
        $password = $this->askValid(
            'Please enter a password for the account',
            'password',
            ['required', 'min:8']
        );
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        if ($this->permissionsEnabled()) {
            Role::firstOrCreate([
                'name' => self::ROLE,
                'guard_name' => 'web'
            ]);
            if ($user->hasRole(self::ROLE)) {
                $this->info("The user account, with email address $email, is already assigned the '" . self::ROLE . "' role\n");
                $response = $this->choice("Do you want to remove the role from the user?", [1 => 'yes', 2 => 'no'], 2);
                if ($response === 'yes') {
                    $user->removeRole(self::ROLE);
                }
            } else {
                $user->assignRole(self::ROLE);
                $this->info("The user account, with email address $email, has been assigned the '" . self::ROLE . "' role\n");
            }
        } else {
            $this->info("Ensured that the user account with email address $email exists\n");
            $this->alert('Since the permissions package has not been installed, the role has not been assigned');
        }
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
