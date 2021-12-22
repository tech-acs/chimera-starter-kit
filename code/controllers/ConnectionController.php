<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnectionRequest;
use App\Models\DatabaseConnection;
use App\Services\ConnectionLoader;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function index()
    {
        $records = DatabaseConnection::orderBy('name')->get();
        return view('connection.index', compact('records'));
    }

    public function create()
    {
        return view('connection.create');
    }

    public function store(ConnectionRequest $request)
    {
        DatabaseConnection::create($request->only(['name', 'host', 'port', 'database', 'username', 'password', 'active']));
        return redirect()->route('connection.index')->withMessage('Connection created');
    }

    public function edit(DatabaseConnection $connection)
    {
        return view('connection.edit', compact('connection'));
    }

    public function update(DatabaseConnection $connection, Request $request)
    {
        $connection->update($request->only(['name', 'host', 'port', 'database', 'username', 'password', 'active']));
        return redirect()->route('connection.index')->withMessage('Connection updated');
    }

    public function destroy(DatabaseConnection $connection)
    {
        $connection->delete();
        return redirect()->route('connection.index')->withMessage('Connection deleted');
    }

    public function test(DatabaseConnection $connection)
    {
        $results = $connection->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('connection.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('connection.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
