<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\QuestionnaireRequest;
use Uneca\Chimera\Models\Questionnaire;

class QuestionnaireController extends Controller
{
    public function index()
    {
        $records = Questionnaire::orderBy('name')->get();
        return view('chimera::developer.questionnaire.index', compact('records'));
    }

    public function create()
    {
        return view('chimera::developer.questionnaire.create');
    }

    public function store(QuestionnaireRequest $request)
    {
        Questionnaire::create($request->only(['name', 'title', 'start_date', 'end_date', 'show_on_home_page', 'host', 'port', 'database', 'username', 'password', 'connection_active']));
        return redirect()->route('developer.questionnaire.index')->withMessage('Record created');
    }

    public function edit(Questionnaire $questionnaire)
    {
        return view('chimera::developer.questionnaire.edit', compact('questionnaire'));
    }

    public function update(Questionnaire $questionnaire, QuestionnaireRequest $request)
    {
        $questionnaire->update($request->only(['name', 'title', 'start_date', 'end_date', 'show_on_home_page', 'host', 'port', 'database', 'username', 'password', 'connection_active']));
        return redirect()->route('developer.questionnaire.index')->withMessage('Record updated');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        $questionnaire->delete();
        return redirect()->route('developer.questionnaire.index')->withMessage('Record deleted');
    }

    public function test(Questionnaire $questionnaire)
    {
        $results = $questionnaire->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('developer.questionnaire.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('questionnaire.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
