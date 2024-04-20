<?php

namespace Uneca\Chimera\Commands;

use Uneca\Chimera\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;

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
        note("This command will create a super admin account for you to use. If the account's email address already exists then the account will be updated with the name and password you provide. It will also assign the account the " . self::ROLE . ' role');

        $email = text(
            label: 'Email address',
            placeholder: 'E.g. admin@example.com',
            required: true,
            validate: fn (string $value) => match (true) {
                (filter_var($value, FILTER_VALIDATE_EMAIL) === false) => 'The value you entered is not a valid email address',
                default => null
            },
            //hint: 'This can not be changed later',
        );
        $name = text(
            label: 'Name',
            default: 'Administrator',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            },
            hint: 'Minimum 3 characters. You can also change it later',
        );
        $password = password(
            label: 'Password',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 7 => 'The password must be at least 8 characters.',
                strlen($value) > 255 => 'The password must not exceed 255 characters.',
                default => null
            },
            hint: 'Minimum 8 characters.',
        );
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        if ($this->permissionsEnabled()) {
            Role::firstOrCreate([
                'name' => self::ROLE,
                'guard_name' => 'web'
            ]);
            if ($user->hasRole(self::ROLE)) {
                info("The user account, with email address $email, is already assigned the '" . self::ROLE . "' role\n");
                $response = select(
                    label: "Do you want to remove the role from the user?",
                    options: ['Yes', 'No'],
                    default: 'No'
                );
                if ($response === 'Yes') {
                    $user->removeRole(self::ROLE);
                    info("The '" . self::ROLE . "' role has been removed from the user account");
                }
            } else {
                $user->assignRole(self::ROLE);
                info("The user account has been assigned the '" . self::ROLE . "' role");
            }
        } else {
            info("Ensured that the user account with email address $email exists");
            alert('Since the permissions package has not been installed, the role has not been assigned');
        }
    }
}
