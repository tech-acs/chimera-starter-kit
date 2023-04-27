<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Questionnaire;
use Exception;
use PDO;

class ConnectionLoader
{
    public function __invoke() : void
    {
        try {
            $connections = Questionnaire::active()->get();
        } catch (Exception $exception) {
            $connections = collect([]);
        }
        $keyedConnections = $connections->mapWithKeys(function ($item) {
            $defaultConfig = config('database.connections')[$item['driver']] ?? [];
            $config = [
                ...$defaultConfig,
                'host' => $item['host'],
                'port' => $item['port'],
                'database' => $item['database'],
                'username' => $item['username'],
                'password' => $item['password'],
            ];
            return [$item['name'] => $config];
        });
        foreach ($keyedConnections as $name => $connection) {
            config(["database.connections.$name" => $connection]);
        }
    }
}
