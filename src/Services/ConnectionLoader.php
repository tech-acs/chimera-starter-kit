<?php

namespace Uneca\Chimera\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Uneca\Chimera\Models\DataSource;
use Exception;

class ConnectionLoader
{
    private function isNotDecryptable(DataSource $connection): bool
    {
        try {
            $connection->password;
            return false;
        } catch (DecryptException $e) {
            return true;
        }
    }

    private function isNotConnectible(DataSource $connection): bool
    {
        try {
            $DSN = vsprintf("%s:dbname=%s;host=%s", [
                $connection['driver'],
                $connection['database'],
                $connection['host'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            ]);
            $pdo = new \PDO($DSN, $connection['username'], $connection['password']);
            return false;
        } catch (\PDOException $e) {
            return true;
        }
    }

    public function __invoke() : void
    {
        try {
            $connections = DataSource::active()->get();

            $keyedConnections = $connections->mapWithKeys(function ($connection) {
                if ($this->isNotDecryptable($connection)) {
                    return [$connection['name'] => null];
                }
                $defaultConfig = config('database.connections')[$connection['driver']] ?? [];
                if ($connection['driver'] === 'mysql') {
                    $defaultConfig = [
                        ...$defaultConfig,
                        'modes' => [
                            'NO_ENGINE_SUBSTITUTION'
                        ],
                        'options' => extension_loaded('pdo_mysql') ? array_filter([
                            \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                            \PDO::ATTR_PERSISTENT => true,
                        ]) : [],
                    ];
                }
                $config = [
                    ...$defaultConfig,
                    'host' => $connection['host'],
                    'port' => $connection['port'],
                    'database' => $connection['database'],
                    'username' => $connection['username'],
                    'password' => $connection['password'],
                ];

                if (($connection['driver'] !== 'sqlite') && $this->isNotConnectible($connection)) {
                    return [$connection['name'] => null];
                }

                return [$connection['name'] => $config];
            });

            $keyedConnections->filter()->map(function ($connection, $name) {
                config(["database.connections.$name" => $connection]);
            });

        } catch (Exception $exception) {
            logger('Exception in ConnectionLoader: ' . $exception->getMessage());
        }

    }
}
