<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke()
    {
        try {
            $this->authorize('home', Auth::user());
        } catch (AuthorizationException $authorizationException) {
            return redirect('faq');
        }

        $questionnaires = Questionnaire::showOnHomePage()->get();
        return view('home', compact('questionnaires'));
    }
}
