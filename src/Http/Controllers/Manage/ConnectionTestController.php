<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Questionnaire;
use Illuminate\Http\RedirectResponse;

class ConnectionTestController extends Controller
{
    public function __invoke(Questionnaire $questionnaire): RedirectResponse
    {
        $results = $questionnaire->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('developer.questionnaire.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('developer.questionnaire.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
