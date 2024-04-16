<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Http\RedirectResponse;

class ConnectionTestController extends Controller
{
    public function __invoke(DataSource $dataSource): RedirectResponse
    {
        $results = $dataSource->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('developer.data-source.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('developer.data-source.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
