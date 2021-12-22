<?php

namespace Uneca\Chimera\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class PermissionsToDb extends Command
{
    protected $signature = 'permissions-to-db';

    protected $description = 'Write all the permissions (charts) configured in the chimera config file to the DB (permissions table)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $pages = config('chimera.pages');
        foreach ($pages as $route => $page) {
            try {
                Permission::firstOrCreate(['name' => $page['permission_name'], 'guard_name' => 'web']);
                $this->info("Permission '{$page['permission_name']}' is present in the database");
            } catch (Exception $exception) {
                $this->error("Permission '{$page['permission_name']}' could not be added in the database");
            }
            foreach ($page['indicators'] as $chart => $chartMetadata) {
                try {
                    Permission::firstOrCreate(['name' => $chartMetadata['permission_name'], 'guard_name' => 'web']);
                    $this->info("Permission '{$chartMetadata['permission_name']}' is present in the database");
                } catch (Exception $exception) {
                    $this->error("Permission '{$chartMetadata['permission_name']}' could not be added in the database");
                }
            }
        }
    }
}
