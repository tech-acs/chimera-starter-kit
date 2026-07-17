<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSetup extends Command
{
    protected $signature = 'chimera:demo-setup';

    protected $description = 'Prepare the site for demo mode';

    public function handle()
    {
        $email = config('chimera.demo_email');
        $userClass = config('auth.providers.users.model');

        $user = $userClass::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo User',
                'password' => Hash::make(str()->random(32)),
            ]
        );

        $role = Role::findOrCreate('Super Admin', 'web');
        $user->assignRole($role);

        $this->info("Demo user '{$email}' ready with Super Admin role.");

        foreach (['session' => config('session.driver'), 'cache' => config('cache.default'), 'queue' => config('queue.default')] as $name => $driver) {
            if ($driver === 'database') {
                $this->warn("{$name} driver is 'database'. This may cause errors on a read-only DB.");
            }
        }

        $this->info('Set CHIMERA_DEMO=true and DEMO_ACCOUNT='.$email.' in your .env file.');
        $this->info('Done. Restart the server or clear config cache.');
    }
}
