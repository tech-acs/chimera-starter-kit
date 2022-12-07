<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;

class HomeController extends Controller
{
    public function __invoke()
    {
        $questionnaires = Questionnaire::showOnHomePage()->get();
        return view('chimera::home', compact('questionnaires'));
    }
}
