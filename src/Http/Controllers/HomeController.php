<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Questionnaire;

class HomeController extends Controller
{
    public function __invoke()
    {
        $questionnaires = Questionnaire::active()->showOnHomePage()->orderBy('rank')->get();
        return view('chimera::home', compact('questionnaires'));
    }
}
