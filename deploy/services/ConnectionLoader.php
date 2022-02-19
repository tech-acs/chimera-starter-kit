<?php

namespace App\Services;

use App\Models\Questionnaire;
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
            return [
                $item['name'] => [
                    'driver' => 'mysql',
                    'url' => null,
                    'host' => $item['host'],
                    'port' => $item['port'],
                    'database' => $item['database'],
                    'username' => $item['username'],
                    'password' => $item['password'],
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => null,
                    'modes' => [
                        'NO_ENGINE_SUBSTITUTION'
                    ],
                    'options' => extension_loaded('pdo_mysql') ? array_filter([
                        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                        PDO::ATTR_PERSISTENT => true,
                    ]) : [],
                ]
            ];
        });
        foreach ($keyedConnections as $name => $connection) {
            config(["database.connections.$name" => $connection]);
        }
    }
}
