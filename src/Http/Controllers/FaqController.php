<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Faq;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $records = Faq::orderBy('rank')->get();
        } catch (QueryException $exception) {
            return view('faq.index', ['records' => collect([])]);
        }
        return view('faq.index', compact('records'));
    }
}
