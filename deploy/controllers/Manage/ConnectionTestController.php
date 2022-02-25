<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;

class ConnectionTestController extends Controller
{
    public function test(Questionnaire $questionnaire)
    {
        $results = $questionnaire->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('questionnaire.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('questionnaire.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
